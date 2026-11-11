<?php

namespace App\Services;

use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Models\Setting;
use App\Support\MailSettings;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * Pembayaran QRIS lewat QRcepat (https://qrcepat.com/developers).
 * QRcepat belum punya webhook, jadi status lunas diketahui dengan polling: dari halaman pesanan
 * saat pemesan menunggu, dan dari perintah qris:sync bila halamannya sudah ditutup.
 */
class QrisPayment
{
    private const BASE_URL = 'https://qrcepat.com/api/v1';

    /** Batas QRIS nasional per transaksi (ketentuan Bank Indonesia). */
    public const MAX_AMOUNT = 10_000_000;

    /** Jeda minimal antar-pengecekan satu pesanan. Batas API 60 request per menit per key. */
    private const CHECK_INTERVAL_SECONDS = 5;

    public static function available(): bool
    {
        return Setting::enabled('qris_enabled') && filled(Setting::get('qris_api_key'));
    }

    /** Pakai QR yang masih berlaku; buat baru hanya bila belum ada atau sudah kedaluwarsa. */
    public function start(Booking $booking): void
    {
        $lock = Cache::lock("qris-start-{$booking->id}", 20);
        if (! $lock->get()) {
            return;
        }

        try {
            $booking->refresh();
            if ($booking->qris_status === 'pending') {
                return;
            }
            if ($booking->total > self::MAX_AMOUNT) {
                throw new RuntimeException('Total melebihi batas QRIS Rp10.000.000 per transaksi. Silakan bayar lewat transfer bank.');
            }

            $data = $this->parse($this->request(fn (PendingRequest $http) => $http->post(self::BASE_URL.'/transactions', ['amount' => $booking->total])));

            if (empty($data['order_id']) || ! $this->safeUrl($data['qr_url'] ?? null)) {
                Log::error('Respons QRcepat tidak lengkap', ['booking' => $booking->code]);
                throw new RuntimeException('Layanan QRIS sedang bermasalah. Silakan coba lagi atau bayar lewat transfer bank.');
            }

            $booking->update([
                'qris_order_id' => $data['order_id'],
                'qris_status' => $data['status'] ?? 'pending',
                'qris_url' => $data['qr_url'],
                'qris_amount' => (int) ($data['amount'] ?? $booking->total),
                'qris_fee' => isset($data['fee_amount']) ? (int) $data['fee_amount'] : null,
                'qris_fee_payer' => $data['fee_payer'] ?? null,
                'qris_created_at' => now(),
            ]);
        } finally {
            $lock->release();
        }
    }

    /** Tanyakan status terbaru ke QRcepat dan terapkan ke pesanan. Gangguan jaringan diabaikan, dicoba lagi nanti. */
    public function refresh(Booking $booking): void
    {
        if ($booking->qris_status !== 'pending' || ! Cache::add("qris-check-{$booking->id}", 1, self::CHECK_INTERVAL_SECONDS)) {
            return;
        }

        try {
            $data = $this->parse($this->request(fn (PendingRequest $http) => $http->get(self::BASE_URL.'/transactions/'.rawurlencode($booking->qris_order_id))));
        } catch (RuntimeException $e) {
            Log::warning('Gagal mengecek status QRIS', ['booking' => $booking->code, 'error' => $e->getMessage()]);

            return;
        }

        $status = $data['status'] ?? null;
        if ($status === 'settlement') {
            $this->settle($booking);
        } elseif (in_array($status, ['expire', 'cancel'], true)) {
            $booking->update(['qris_status' => $status]);
        }
    }

    /** Hasil uji kunci API tanpa membuat tagihan: order_id palsu menjawab 404 bila kunci benar, 401 bila salah. */
    public function testKey(string $key): bool
    {
        try {
            $response = Http::withToken($key)->acceptJson()->timeout(15)->get(self::BASE_URL.'/transactions/CEK-KONEKSI-GILISEKOTONG');
        } catch (ConnectionException) {
            throw new RuntimeException('Tidak bisa menghubungi QRcepat. Periksa koneksi server.');
        }

        return in_array($response->status(), [200, 404], true);
    }

    private function settle(Booking $booking): void
    {
        $confirmed = DB::transaction(function () use ($booking) {
            $fresh = Booking::query()->lockForUpdate()->findOrFail($booking->id);
            if ($fresh->qris_status === 'settlement') {
                return false;
            }

            if (in_array($fresh->status, [Booking::STATUS_UNPAID, Booking::STATUS_REVIEW], true)) {
                $fresh->update([
                    'qris_status' => 'settlement',
                    'status' => Booking::STATUS_CONFIRMED,
                    'payment_method' => 'qris',
                    'paid_at' => now(),
                    'verified_at' => now(),
                ]);

                return true;
            }

            // Uang masuk padahal pesanan sudah dibatalkan atau sudah lunas lewat transfer: admin perlu mengembalikannya.
            $fresh->update([
                'qris_status' => 'settlement',
                'admin_note' => "QRIS {$fresh->qris_order_id} lunas saat pesanan berstatus {$fresh->statusLabel()}. Periksa dan kembalikan dana bila perlu.",
            ]);
            Log::warning('QRIS lunas di luar alur pembayaran', ['booking' => $fresh->code]);

            return false;
        });

        $booking->refresh();
        if ($confirmed) {
            MailSettings::notify($booking->user->email, new BookingConfirmedMail($booking));
        }
    }

    private function request(callable $send): Response
    {
        $http = Http::withToken((string) Setting::get('qris_api_key'))->acceptJson()->asJson()->timeout(20);

        try {
            return $send($http);
        } catch (ConnectionException) {
            throw new RuntimeException('Tidak bisa menghubungi layanan QRIS. Silakan coba lagi sebentar lagi.');
        }
    }

    private function parse(Response $response): array
    {
        if ($response->successful() && is_array($response->json())) {
            return $response->json();
        }

        if (in_array($response->status(), [401, 403], true)) {
            Log::error('QRcepat menolak kunci API', ['status' => $response->status()]);
            throw new RuntimeException('Pembayaran QRIS sedang tidak tersedia. Silakan bayar lewat transfer bank.');
        }
        if ($response->status() === 422 && is_string($message = $response->json('message'))) {
            throw new RuntimeException($message);
        }

        throw new RuntimeException('Layanan QRIS sedang sibuk. Silakan coba lagi sebentar lagi.');
    }

    /** qr_url dipakai sebagai src gambar dan tautan, jadi hanya https yang diterima. */
    private function safeUrl(mixed $url): bool
    {
        return is_string($url) && str_starts_with($url, 'https://') && filter_var($url, FILTER_VALIDATE_URL) !== false && strlen($url) <= 500;
    }
}
