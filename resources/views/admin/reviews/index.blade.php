<x-layouts.admin title="Ulasan">
    @if ($average)
        <p class="mb-4 flex items-center gap-2 text-sm text-stone-600"><x-stars :value="$average"/><span class="num font-semibold text-sea-950">{{ number_format($average, 1, ',', '') }}</span> rata-rata ulasan yang tampil</p>
    @endif
    <div class="space-y-3">
        @forelse ($reviews as $review)
            <div @class(['card flex flex-col gap-3 p-5 sm:flex-row sm:items-start sm:justify-between', 'opacity-60' => ! $review->is_visible])>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <x-stars :value="$review->rating"/>
                        @unless ($review->is_visible)<span class="chip tone-none">Disembunyikan</span>@endunless
                    </div>
                    @if ($review->comment)<p class="mt-2 text-sm whitespace-pre-line text-stone-700">{{ $review->comment }}</p>@endif
                    <p class="mt-2 text-xs text-stone-500">
                        {{ $review->user->name }} · {{ $review->boat->name }} ·
                        <a href="{{ route('admin.bookings.show', $review->booking) }}" class="num font-mono font-semibold text-sea-700 hover:underline">{{ $review->booking->code }}</a>
                        · {{ $review->created_at->translatedFormat('d M Y') }}
                    </p>
                </div>
                <form action="{{ route('admin.reviews.toggle', $review) }}" method="post" class="shrink-0">
                    @csrf
                    <button class="btn-ghost btn-sm">{{ $review->is_visible ? 'Sembunyikan' : 'Tampilkan' }}</button>
                </form>
            </div>
        @empty
            <div class="card p-10 text-center text-sm text-stone-600">Belum ada ulasan. Pemesan bisa memberi ulasan setelah pesanannya selesai.</div>
        @endforelse
    </div>
    <div class="mt-6">{{ $reviews->links() }}</div>
</x-layouts.admin>
