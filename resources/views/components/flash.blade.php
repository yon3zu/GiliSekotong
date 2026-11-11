@foreach (['success' => 'border-sea-200 bg-sea-50 text-sea-900', 'info' => 'border-sand-300 bg-sand-100 text-ink'] as $key => $style)
    @if (session($key))
        <div x-data="{ open: true }" x-show="open" x-transition.opacity role="status"
             class="mb-6 flex items-start gap-3 rounded-xl border px-4 py-3 text-sm {{ $style }}">
            <x-icon name="{{ $key === 'success' ? 'check' : 'info' }}" class="mt-0.5 size-4 shrink-0"/>
            <p class="flex-1">{{ session($key) }}</p>
            <button type="button" @click="open = false" class="opacity-60 hover:opacity-100" aria-label="Tutup"><x-icon name="x" class="size-4"/></button>
        </div>
    @endif
@endforeach
