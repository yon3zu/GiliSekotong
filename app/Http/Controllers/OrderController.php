<?php

namespace App\Http\Controllers;

use App\Mail\PaymentProofMail;
use App\Models\Booking;
use App\Support\MailSettings;
use App\Models\Setting;
use App\Services\QrisPayment;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $bookings = $request->user()->bookings()->with(['boat', 'review'])->latest()->paginate(10);

        return view('orders.index', compact('bookings'));
    }

    public function show(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        $booking->load(['boat', 'meals', 'rentals', 'seaCondition', 'review']);

        return view('orders.show', [
            'booking' => $booking,
            'bank' => [
                'name' => Setting::get('bank_name'),
                'account' => Setting::get('bank_account'),
                'holder' => Setting::get('bank_holder'),
            ],
            'deadline' => $booking->created_at->addHours((int) Setting::get('payment_deadline_hours')),
        ]);
    }

    public function uploadProof(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        abort_unless($booking->canUploadProof(), 403);

        $request->validate([
            'proof' => ['required', 'image', 'max:3072'],
        ], ['proof.max' => 'Ukuran foto bukti transfer maksimal 3 MB.']);

        if ($booking->payment_proof) {
            Storage::disk('local')->delete($booking->payment_proof);
        }

        $booking->update([
            'payment_proof' => $request->file('proof')->store('payment-proofs', 'local'),
            'paid_at' => now(),
            'status' => Booking::STATUS_REVIEW,
        ]);

        MailSettings::notify(MailSettings::adminAddress(), new PaymentProofMail($booking));

        return back()->with('success', 'Bukti transfer terkirim. Admin akan memverifikasi dalam waktu singkat.');
    }

    /** Bukti transfer disimpan privat dan hanya bisa dilihat pemilik pesanan atau staf. */
    public function proof(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        abort_unless($booking->payment_proof && Storage::disk('local')->exists($booking->payment_proof), 404);

        return Storage::disk('local')->response($booking->payment_proof);
    }

    public function cancel(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        abort_unless($booking->status === Booking::STATUS_UNPAID, 403);

        $booking->update(['status' => Booking::STATUS_CANCELLED]);

        return back()->with('success', 'Pesanan dibatalkan.');
    }

    public function startQris(Request $request, Booking $booking, QrisPayment $qris)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        abort_unless($booking->status === Booking::STATUS_UNPAID && QrisPayment::available(), 403);

        try {
            $qris->start($booking);
        } catch (RuntimeException $e) {
            return back()->withErrors(['qris' => $e->getMessage()]);
        }

        return back();
    }

    /** Dipanggil berkala oleh halaman pesanan selama QR ditampilkan. */
    public function qrisStatus(Request $request, Booking $booking, QrisPayment $qris)
    {
        $this->authorizeOwner($request, $booking);

        $qris->refresh($booking);
        $booking->refresh();

        return response()->json(['status' => $booking->qris_status, 'booking' => $booking->status]);
    }

    public function complete(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        abort_unless($booking->canBeCompletedByCustomer(), 403);

        $booking->markCompleted();

        return back()->with('success', 'Pesanan selesai. Terima kasih sudah berlayar bersama kami, ceritakan pengalaman Anda di bawah.');
    }

    /** Satu pesanan satu ulasan; mengirim ulang berarti mengubah ulasan yang sama. */
    public function review(Request $request, Booking $booking)
    {
        abort_unless($booking->user_id === $request->user()->id, 404);
        abort_unless($booking->status === Booking::STATUS_DONE, 403);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], ['rating.required' => 'Pilih jumlah bintang terlebih dahulu.']);

        $booking->review()->updateOrCreate([], [...$data, 'user_id' => $booking->user_id, 'boat_id' => $booking->boat_id]);

        return back()->with('success', 'Terima kasih, ulasan Anda tersimpan.');
    }

    public function ticket(Request $request, Booking $booking)
    {
        $this->authorizeOwner($request, $booking);
        abort_unless($booking->hasTicket(), 403, 'E-tiket terbit setelah pembayaran diverifikasi.');
        $booking->load(['boat', 'meals', 'rentals', 'seaCondition']);

        $writer = new Writer(new ImageRenderer(new RendererStyle(220, 1), new SvgImageBackEnd));
        $qr = $writer->writeString(route('admin.checkin', ['kode' => $booking->code]));
        $qr = preg_replace('/^<\?xml.*?\?>\s*/', '', $qr);

        return view('orders.ticket', compact('booking', 'qr'));
    }

    private function authorizeOwner(Request $request, Booking $booking): void
    {
        abort_unless($booking->user_id === $request->user()->id || $request->user()->isStaff(), 404);
    }
}
