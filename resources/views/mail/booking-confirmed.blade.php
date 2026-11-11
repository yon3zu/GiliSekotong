<x-mail::message>
# E-tiket Anda sudah terbit

Halo {{ $booking->contact_name }}, pembayaran pesanan **{{ $booking->code }}** sudah kami verifikasi.

**Perahu:** {{ $booking->boat->name }} ({{ $booking->tripLabel() }})<br>
**Tanggal:** {{ $booking->trip_date->translatedFormat('l, d F Y') }}, berangkat {{ $booking->departure_time }} WITA<br>
**Penumpang:** {{ $booking->passengers }} orang

Tunjukkan kode QR pada e-tiket kepada petugas di Dermaga Tawun, 30 menit sebelum berangkat.

<x-mail::button :url="route('orders.ticket', $booking)">
Buka e-tiket
</x-mail::button>

Pantau kondisi laut sebelum berangkat di halaman [Kondisi Laut & Cuaca]({{ route('sea') }}). Bila laut dinilai tidak layak, kami akan menghubungi Anda untuk menjadwalkan ulang tanpa biaya.

Sampai jumpa di Sekotong,<br>
GiliSekotong
</x-mail::message>
