<x-layouts.app title="Kontak & FAQ" :schema="[\App\Support\Faq::schema()]">
    <section class="border-b border-sand-200 bg-sand-100">
        <div class="container-x py-12 sm:py-16">
            <p class="eyebrow">Kontak</p>
            <h1 class="h-display mt-3 text-4xl sm:text-5xl">Ada pertanyaan? Tanya saja.</h1>
        </div>
    </section>
    <div class="container-x grid gap-10 py-14 lg:grid-cols-[1fr_1.4fr]">
        <div class="space-y-4">
            <a href="https://wa.me/{{ \App\Models\Setting::get('contact_whatsapp') }}" target="_blank" rel="noopener" class="card flex items-center gap-4 p-5 hover:border-sea-300">
                <span class="flex size-12 items-center justify-center rounded-full bg-[#25D366] text-white"><x-icon name="whatsapp" class="size-6"/></span>
                <span><span class="block text-xs text-stone-500">WhatsApp (respon tercepat)</span><span class="font-semibold">{{ \App\Models\Setting::get('contact_phone') }}</span></span>
            </a>
            <a href="mailto:{{ \App\Models\Setting::get('contact_email') }}" class="card flex items-center gap-4 p-5 hover:border-sea-300">
                <span class="flex size-12 items-center justify-center rounded-full bg-sea-800 text-white"><x-icon name="mail" class="size-6"/></span>
                <span><span class="block text-xs text-stone-500">Email</span><span class="font-semibold">{{ \App\Models\Setting::get('contact_email') }}</span></span>
            </a>
            <div class="card flex items-start gap-4 p-5">
                <span class="flex size-12 shrink-0 items-center justify-center rounded-full bg-coral-500 text-white"><x-icon name="pin" class="size-6"/></span>
                <span><span class="block text-xs text-stone-500">Titik kumpul</span><span class="font-semibold">{{ \App\Models\Setting::get('address') }}</span>
                    <a href="https://www.google.com/maps/search/?api=1&query={{ urlencode('Dermaga Tawun Sekotong') }}" target="_blank" rel="noopener" class="mt-1 block text-sm font-semibold text-sea-700 hover:underline">Buka di Google Maps</a></span>
            </div>
            <p class="px-1 text-sm text-stone-600">Kantor dermaga buka setiap hari 07.00–17.00 WITA.</p>
        </div>

        <div>
            <h2 class="h-display text-3xl">Pertanyaan yang sering muncul</h2>
            <div class="mt-6 divide-y divide-sand-200 rounded-2xl border border-sand-200 bg-white">
                @foreach (\App\Support\Faq::items() as [$q, $a])
                    <details class="group p-5">
                        <summary class="flex cursor-pointer list-none items-center justify-between gap-4 font-semibold">
                            {{ $q }}
                            <span class="text-xl leading-none text-coral-500 transition group-open:rotate-45">+</span>
                        </summary>
                        <p class="mt-3 text-sm leading-relaxed text-stone-600">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </div>
</x-layouts.app>
