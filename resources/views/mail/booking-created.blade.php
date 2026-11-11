<x-mail::message>
# Pesanan Anda kami terima

Halo {{ $booking->contact_name }}, terima kasih telah memesan perahu di GiliSekotong.

**Kode pesanan:** {{ $booking->code }}<br>
**Tanggal:** {{ $booking->trip_date->translatedFormat('l, d F Y') }}, berangkat {{ $booking->departure_time }} WITA<br>
**Penumpang:** {{ $booking->passengers }} orang

@include('mail._lines')

Silakan transfer **tepat {{ rupiah($booking->transferAmount()) }}** (sudah termasuk kode unik {{ $booking->uniqueCodeLabel() }}, jangan dibulatkan) ke rekening berikut dalam {{ \App\Models\Setting::get('payment_deadline_hours') }} jam:

**{{ \App\Models\Setting::get('bank_name') }} {{ \App\Models\Setting::get('bank_account') }}**<br>
a.n. {{ \App\Models\Setting::get('bank_holder') }}

Setelah transfer, unggah foto bukti transfer di halaman pesanan.
@if (\App\Services\QrisPayment::available())
Bisa juga bayar dengan QRIS dari halaman pesanan, tanpa unggah bukti.
@endif

<x-mail::button :url="route('orders.show', $booking)">
Unggah bukti transfer
</x-mail::button>

Salam,<br>
GiliSekotong · Dermaga Tawun, Sekotong
</x-mail::message>
