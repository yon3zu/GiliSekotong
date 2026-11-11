<x-layouts.app title="Destinasi 3 Gili" description="Mengenal Gili Nanggu, Gili Sudak, dan Gili Kedis di Sekotong, Lombok Barat." :schema="[\App\Support\Seo::destinationsSchema()]">
    <section class="border-b border-sand-200 bg-sand-100">
        <div class="container-x py-12 sm:py-16">
            <p class="eyebrow">Gili-gili selatan</p>
            <h1 class="h-display mt-3 max-w-3xl text-4xl sm:text-5xl">Lebih sepi dari Gili Trawangan, airnya sama jernihnya.</h1>
            <p class="mt-4 max-w-2xl text-lg text-stone-700">Di barat daya Lombok ada puluhan gili kecil. Tiga yang paling mudah dijangkau dari Dermaga Tawun bisa dikunjungi dalam satu hari.</p>
        </div>
    </section>

    @php
        $islands = [
            [
                'name' => 'Gili Nanggu', 'size' => 1, 'tone' => '#246b4a', 'time' => '± 15 menit dari dermaga',
                'text' => 'Gili terbesar di antara ketiganya dan biasanya jadi persinggahan pertama. Pasir putihnya landai, air di sisi timur tenang, dan terumbu karang dangkal bisa dinikmati tanpa harus pandai berenang.',
                'do' => ['Snorkeling di karang dangkal', 'Memberi makan ikan dari tepi', 'Berteduh di bawah cemara laut'],
            ],
            [
                'name' => 'Gili Sudak', 'size' => .85, 'tone' => '#1f7350', 'time' => '± 10 menit dari Nanggu',
                'text' => 'Gili yang paling ramai warung. Di sinilah biasanya rombongan makan siang: ikan bakar dari tangkapan nelayan pagi itu, plecing kangkung, dan sambal beberuk.',
                'do' => ['Makan siang di gazebo pantai', 'Bermain air di pantai berpasir', 'Kelapa muda langsung dari pohon'],
            ],
            [
                'name' => 'Gili Kedis', 'size' => .45, 'tone' => '#2f8a5f', 'time' => '± 5 menit dari Sudak',
                'text' => 'Pulau mungil yang bisa dikelilingi berjalan kaki dalam beberapa menit. Karena ukurannya, foto dari perahu dengan latar seluruh pulau jadi andalan tamu.',
                'do' => ['Foto satu pulau utuh dari perahu', 'Snorkeling di tubir karang', 'Duduk santai menunggu perahu'],
            ],
        ];
    @endphp

    <div class="container-x space-y-16 py-14 sm:space-y-24 sm:py-20">
        @foreach ($islands as $i => $island)
            <article class="grid items-center gap-8 md:grid-cols-2 md:gap-14">
                <div @class(['overflow-hidden rounded-3xl', 'md:order-2' => $i % 2])>
                    <x-island :size="$island['size']" :tone="$island['tone']" class="w-full"/>
                </div>
                <div>
                    <p class="num text-sm font-bold text-coral-600">0{{ $i + 1 }} · {{ $island['time'] }}</p>
                    <h2 class="h-display mt-2 text-3xl sm:text-4xl">{{ $island['name'] }}</h2>
                    <p class="mt-4 leading-relaxed text-stone-700">{{ $island['text'] }}</p>
                    <ul class="mt-5 space-y-2">
                        @foreach ($island['do'] as $item)
                            <li class="flex items-center gap-2.5 text-sm"><span class="flex size-6 items-center justify-center rounded-full bg-sea-50 text-sea-700"><x-icon name="check" class="size-3.5"/></span>{{ $item }}</li>
                        @endforeach
                    </ul>
                </div>
            </article>
        @endforeach
    </div>

    <section class="container-x">
        <div class="rounded-3xl bg-sea-900 p-8 text-white sm:p-12">
            <h2 class="h-display text-3xl text-white">Menjaga karang tetap hidup</h2>
            <div class="mt-6 grid gap-6 text-sm text-sea-100 sm:grid-cols-3">
                <p><strong class="block text-white">Jangan injak karang.</strong> Gunakan pelampung bila belum lancar berenang, supaya tidak perlu berdiri di atas karang.</p>
                <p><strong class="block text-white">Bawa pulang sampah.</strong> Perahu kami menyediakan kantong sampah. Gili kecil tidak punya tempat pembuangan.</p>
                <p><strong class="block text-white">Ikan bukan untuk dibawa.</strong> Memberi makan secukupnya, memotret sepuasnya, mengambil tidak sama sekali.</p>
            </div>
        </div>
    </section>
</x-layouts.app>
