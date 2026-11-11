@props(['review', 'showBoat' => false])
<figure class="card flex flex-col gap-3 p-5">
    <x-stars :value="$review->rating"/>
    @if ($review->comment)
        <blockquote class="text-sm leading-relaxed whitespace-pre-line text-stone-700">{{ $review->comment }}</blockquote>
    @endif
    <figcaption class="mt-auto text-xs text-stone-500">
        <span class="font-semibold text-sea-950">{{ $review->publicName() }}</span>
        @if ($showBoat) · {{ $review->boat->name }} @endif
        · {{ $review->created_at->translatedFormat('M Y') }}
    </figcaption>
</figure>
