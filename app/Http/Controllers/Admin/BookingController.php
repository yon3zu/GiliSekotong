<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\BookingConfirmedMail;
use App\Models\Booking;
use App\Support\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::query()
            ->with(['boat', 'user'])
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('tanggal'), fn ($q, $d) => $q->whereDate('trip_date', $d))
            ->when($request->query('q'), fn ($q, $term) => $q->where(fn ($w) => $w
                ->where('code', 'like', "%{$term}%")
                ->orWhere('contact_name', 'like', "%{$term}%")
                ->orWhere('contact_phone', 'like', "%{$term}%")
                // Nominal dari mutasi bank, mis. "350.067".
                ->when(preg_match('/^[\d.,\s]+$/', $term), fn ($amt) => $amt->orWhereRaw('total + unique_code = ?', [(int) preg_replace('/\D/', '', $term)]))))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $counts = Booking::query()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        return view('admin.bookings.index', compact('bookings', 'counts'));
    }

    public function show(Booking $booking)
    {
        $booking->load(['boat', 'user', 'meals', 'rentals', 'verifier', 'seaCondition', 'review']);

        return view('admin.bookings.show', compact('booking'));
    }

    public function status(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['verify', 'reject', 'checkin', 'complete', 'cancel'])],
            'admin_note' => ['nullable', 'string', 'max:255'],
        ]);

        $allowed = match ($data['action']) {
            'verify' => in_array($booking->status, [Booking::STATUS_UNPAID, Booking::STATUS_REVIEW], true),
            'reject' => $booking->status === Booking::STATUS_REVIEW,
            'checkin' => $booking->status === Booking::STATUS_CONFIRMED && ! $booking->checked_in_at,
            'complete' => $booking->status === Booking::STATUS_CONFIRMED,
            'cancel' => ! in_array($booking->status, [Booking::STATUS_DONE, Booking::STATUS_CANCELLED], true),
        };
        abort_unless($allowed, 422, 'Aksi tidak sesuai dengan status pesanan.');

        $changes = match ($data['action']) {
            'verify' => ['status' => Booking::STATUS_CONFIRMED, 'payment_method' => 'transfer', 'paid_at' => $booking->paid_at ?? now(), 'verified_at' => now(), 'verified_by' => $request->user()->id],
            'reject' => ['status' => Booking::STATUS_UNPAID, 'paid_at' => null],
            'checkin' => ['checked_in_at' => now()],
            'complete' => ['status' => Booking::STATUS_DONE, 'completed_at' => now(), 'checked_in_at' => $booking->checked_in_at ?? now()],
            'cancel' => ['status' => Booking::STATUS_CANCELLED],
        };

        $booking->update([...$changes, 'admin_note' => $data['admin_note'] ?? $booking->admin_note]);

        if ($data['action'] === 'verify') {
            MailSettings::notify($booking->user->email, new BookingConfirmedMail($booking));
        }

        $messages = [
            'verify' => 'Pembayaran diverifikasi. E-tiket sudah terbit untuk pemesan.',
            'reject' => 'Bukti transfer ditolak. Pemesan diminta mengunggah ulang.',
            'checkin' => 'Rombongan sudah check-in di dermaga.',
            'complete' => 'Trip ditandai selesai.',
            'cancel' => 'Pesanan dibatalkan.',
        ];

        return back()->with('success', $messages[$data['action']]);
    }

    /** Pindah tanggal, mis. saat pohon keputusan menilai laut tidak layak. */
    public function reschedule(Request $request, Booking $booking)
    {
        $data = $request->validate([
            'trip_date' => ['required', 'date', 'after_or_equal:today'],
            'departure_time' => ['required', Rule::in(Booking::DEPARTURE_TIMES)],
        ]);

        if ($booking->boat->isBookedOn($data['trip_date'], $booking->id)) {
            return back()->withErrors(['trip_date' => 'Perahu sudah terpakai di tanggal tersebut.']);
        }

        $booking->update([
            ...$data,
            'admin_note' => 'Dijadwalkan ulang dari '.$booking->trip_date->translatedFormat('d M Y'),
        ]);

        return back()->with('success', 'Jadwal trip dipindahkan.');
    }

    /** Halaman check-in di dermaga: hasil pindai QR e-tiket membuka halaman ini. */
    public function checkin(Request $request)
    {
        $code = strtoupper(trim((string) $request->query('kode')));
        $booking = $code ? Booking::query()->with(['boat', 'meals', 'rentals'])->where('code', $code)->first() : null;

        return view('admin.bookings.checkin', compact('code', 'booking'));
    }
}
