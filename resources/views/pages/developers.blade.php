<x-layouts.app title="Tim Pengembang" description="Dosen dan mahasiswa Universitas Bumigora yang membangun GiliSekotong.">
    <section class="relative isolate overflow-hidden bg-sea-900 text-white">
        @if ($banner)
            <img src="{{ asset('storage/'.$banner) }}" alt="Foto tim pengembang GiliSekotong" class="absolute inset-0 -z-10 size-full object-cover">
            <div class="absolute inset-0 -z-10 bg-gradient-to-t from-sea-950/90 via-sea-950/50 to-sea-950/20"></div>
        @endif
        <div @class(['container-x flex items-end justify-between gap-6 py-14 sm:py-20', 'min-h-[22rem] sm:min-h-[30rem]' => $banner])>
            <div class="max-w-2xl">
                <p class="eyebrow text-coral-400">Di balik GiliSekotong</p>
                <h1 class="h-display mt-3 text-4xl text-white drop-shadow sm:text-5xl">Tim pengembang</h1>
                <p class="mt-4 text-lg leading-relaxed text-sea-100 drop-shadow">Dosen dan mahasiswa Universitas Bumigora yang membangun platform ini untuk perahu warga Dusun Tawun, Sekotong Barat.</p>
            </div>
            <img src="{{ asset('images/aksara.webp') }}" alt="AKSARA Universitas Bumigora" width="892" height="828" class="hidden w-40 shrink-0 rounded-2xl shadow-lg sm:block lg:w-52">
        </div>
        <div class="wave-edge h-3"></div>
    </section>

    @if ($lecturers->isNotEmpty())
        <section class="container-x pt-14">
            <h2 class="h-display text-3xl">Dosen</h2>
            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($lecturers as $person)
                    <x-person-card :name="$person->name" :tag="$person->tag" :photo="$person->photoUrl()"/>
                @endforeach
            </div>
        </section>
    @endif

    <section class="container-x pt-14">
        <h2 class="h-display text-3xl">Mahasiswa</h2>
        <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <x-person-card lead :name="\App\Models\Developer::LEAD['name']" :tag="\App\Models\Developer::LEAD['tag']" :photo="$leadPhoto ? asset('storage/'.$leadPhoto) : null"/>
            @foreach ($students as $person)
                <x-person-card :name="$person->name" :tag="$person->tag" :photo="$person->photoUrl()"/>
            @endforeach
        </div>
    </section>

    <section class="container-x pt-10 sm:hidden">
        <img src="{{ asset('images/aksara.webp') }}" alt="AKSARA Universitas Bumigora" width="892" height="828" loading="lazy" class="mx-auto w-40 rounded-2xl">
    </section>
</x-layouts.app>
