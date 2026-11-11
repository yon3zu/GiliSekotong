<x-mail::message>
# Bukti transfer baru perlu diverifikasi

**{{ $booking->contact_name }}** ({{ $booking->contact_phone }}) mengunggah bukti transfer untuk pesanan **{{ $booking->code }}**.

**Perahu:** {{ $booking->boat->name }} · {{ $booking->trip_date->translatedFormat('d M Y') }} · {{ $booking->passengers }} orang<br>
**Nominal transfer:** {{ rupiah($booking->transferAmount()) }} (total {{ rupiah($booking->total) }} + kode unik {{ $booking->uniqueCodeLabel() }})

<x-mail::button :url="route('admin.bookings.show', $booking)">
Periksa pesanan
</x-mail::button>
</x-mail::message>
