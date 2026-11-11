<div @class(['card overflow-hidden', 'opacity-60' => ! $item->is_active])>
    <div class="relative aspect-video bg-sea-900">
        @if ($item->stillUrl())
            <img src="{{ $item->stillUrl() }}" alt="{{ $item->title }}" loading="lazy" class="h-full w-full object-cover">
        @elseif ($item->isVideo())
            <video src="{{ $item->url() }}" muted preload="metadata" class="h-full w-full object-cover"></video>
        @endif
        <span class="absolute top-2 left-2 chip bg-white/90 text-sea-900">{{ $item->isVideo() ? 'Video' : 'Foto' }} · #{{ $item->sort }}</span>
        @unless ($item->is_active)<span class="absolute top-2 right-2 chip tone-none">Disembunyikan</span>@endunless
        @if ($item->isVideo() && ! $item->poster_path)<span class="absolute right-2 bottom-2 chip tone-waspada">Belum ada sampul</span>@endif
    </div>
    <div class="p-3">
        <p class="truncate text-sm font-semibold">{{ $item->title }}</p>
        <p class="text-xs text-stone-500">{{ $item->location ?? 'Tanpa lokasi' }}</p>
        <div class="mt-2 flex gap-2">
            <a href="{{ route('admin.media.edit', $item) }}" class="btn-ghost btn-sm">Ubah</a>
            <form action="{{ route('admin.media.destroy', $item) }}" method="post" onsubmit="return confirm('Hapus media ini?')">@csrf @method('delete')<button class="btn-ghost btn-sm text-coral-700">Hapus</button></form>
        </div>
    </div>
</div>
