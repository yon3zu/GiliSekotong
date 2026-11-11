<x-mail::table>
| Rincian | Jumlah |
|:--|--:|
| {{ $booking->tripLabel() }} · {{ $booking->boat->name }} | {{ rupiah($booking->trip_price) }} |
@foreach ($booking->meals as $m)
| {{ $m->name }} × {{ $m->qty }} | {{ rupiah($m->subtotal) }} |
@endforeach
@foreach ($booking->rentals as $r)
| {{ $r->name }} × {{ $r->qty }} | {{ rupiah($r->subtotal) }} |
@endforeach
| **Total** | **{{ rupiah($booking->total) }}** |
</x-mail::table>
