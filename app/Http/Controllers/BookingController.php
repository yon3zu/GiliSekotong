<?php

namespace App\Http\Controllers;

use App\Models\Boat;
use App\Models\Booking;
use App\Models\SeaCondition;
use App\Mail\BookingCreatedMail;
use App\Services\BookingQuote;
use App\Support\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    /** Simpan pilihan ke sesi lalu lanjut ke checkout (login tidak diperlukan di langkah ini). */
    public function draft(Request $request)
    {
        $boat = Boat::query()->where('is_active', true)->findOrFail($request->integer('boat_id'));

        $maxPerItem = min(max($request->integer('passengers'), 1), $boat->capacity);

        $data = $request->validate([
            'boat_id' => ['required'],
            'trip_type' => ['required', Rule::in(array_keys(Booking::TRIP_TYPES))],
            'trip_date' => ['required', 'date', 'after:today', 'before_or_equal:'.today()->addDays(90)->toDateString()],
            'departure_time' => ['required', Rule::in(Booking::DEPARTURE_TIMES)],
            'passengers' => ['required', 'integer', 'min:1', 'max:'.$boat->capacity],
            'meals' => ['nullable', 'array'],
            'meals.*' => ['nullable', 'integer', 'min:0', 'max:20'],
            'rentals' => ['nullable', 'array'],
            'rentals.*' => ['nullable', 'integer', 'min:0', 'max:'.$maxPerItem],
        ], [
            'trip_date.after' => 'Pemesanan paling cepat untuk besok (H-1).',
            'trip_date.before_or_equal' => 'Pemesanan maksimal 90 hari ke depan.',
            'passengers.max' => "Kapasitas {$boat->name} maksimal {$boat->capacity} orang.",
            'rentals.*.max' => 'Jumlah sewa tiap alat paling banyak sama dengan jumlah penumpang.',
        ]);

        $this->ensureBookable($boat, $data['trip_date']);

        $request->session()->put('booking_draft', [
            'boat_id' => $boat->id,
            'trip_type' => $data['trip_type'],
            'trip_date' => $data['trip_date'],
            'departure_time' => $data['departure_time'],
            'passengers' => (int) $data['passengers'],
            'meals' => array_map('intval', array_filter($data['meals'] ?? [])),
            'rentals' => array_map('intval', array_filter($data['rentals'] ?? [])),
        ]);

        return redirect()->route('booking.checkout');
    }

    public function checkout(Request $request)
    {
        $draft = $request->session()->get('booking_draft');
        if (! $draft || ! ($boat = Boat::find($draft['boat_id']))) {
            return redirect()->route('boats.index')->with('info', 'Pilih perahu terlebih dahulu untuk mulai memesan.');
        }

        return view('booking.checkout', [
            'draft' => $draft,
            'quote' => BookingQuote::make($boat, $draft['passengers'], $draft['meals'], $draft['rentals'] ?? []),
            'condition' => SeaCondition::query()->whereDate('date', $draft['trip_date'])->first(),
        ]);
    }

    public function confirm(Request $request)
    {
        $draft = $request->session()->get('booking_draft');
        abort_unless($draft, 404);

        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:100'],
            'contact_phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{9,20}$/'],
            'notes' => ['nullable', 'string', 'max:500'],
            'agree' => ['accepted'],
        ], [
            'contact_phone.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
            'agree.accepted' => 'Centang persetujuan ketentuan perjalanan terlebih dahulu.',
        ]);

        $boat = Boat::query()->where('is_active', true)->findOrFail($draft['boat_id']);

        $booking = DB::transaction(function () use ($boat, $draft, $data, $request) {
            // Kunci baris perahu agar dua pemesan tidak mendapat tanggal yang sama.
            Boat::query()->whereKey($boat->id)->lockForUpdate()->first();
            $this->ensureBookable($boat, $draft['trip_date']);

            $quote = BookingQuote::make($boat, $draft['passengers'], $draft['meals'], $draft['rentals'] ?? []);

            $booking = Booking::create([
                'code' => Booking::generateCode(),
                'user_id' => $request->user()->id,
                'boat_id' => $boat->id,
                'trip_type' => $draft['trip_type'] ?? 'gili',
                'trip_date' => $draft['trip_date'],
                'departure_time' => $draft['departure_time'],
                'passengers' => $draft['passengers'],
                'contact_name' => $data['contact_name'],
                'contact_phone' => $data['contact_phone'],
                'notes' => $data['notes'] ?? null,
                'trip_price' => $quote['trip_price'],
                'meal_total' => $quote['meal_total'],
                'rental_total' => $quote['rental_total'],
                'total' => $quote['total'],
                'status' => Booking::STATUS_UNPAID,
            ]);

            $booking->saveLines($quote);

            return $booking;
        });

        if (! $request->user()->phone) {
            $request->user()->update(['phone' => $data['contact_phone']]);
        }

        $request->session()->forget('booking_draft');
        MailSettings::notify($request->user()->email, new BookingCreatedMail($booking));

        return redirect()->route('orders.show', $booking)
            ->with('success', 'Pesanan dibuat. Selesaikan pembayaran agar perahu terkunci untuk Anda.');
    }

    public function discard(Request $request)
    {
        $request->session()->forget('booking_draft');

        return redirect()->route('boats.index');
    }

    private function ensureBookable(Boat $boat, string $date): void
    {
        if ($boat->isBookedOn($date)) {
            throw ValidationException::withMessages([
                'trip_date' => "{$boat->name} sudah dipesan rombongan lain pada tanggal tersebut. Coba tanggal atau perahu lain.",
            ]);
        }

        $condition = SeaCondition::query()->whereDate('date', $date)->first();
        if ($condition?->blocksBooking()) {
            throw ValidationException::withMessages([
                'trip_date' => 'Kondisi laut pada tanggal tersebut dinilai tidak layak untuk berlayar. Silakan pilih tanggal lain.',
            ]);
        }
    }
}
