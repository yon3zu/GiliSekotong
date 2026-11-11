<?php

/*
 * Integrasi data terbuka BMKG.
 * Data diambil berkala lalu disimpan ke database; halaman web hanya membaca database
 * sehingga server BMKG tidak dibebani setiap kali ada pengunjung.
 */
return [
    'user_agent' => 'GiliSekotong/1.0 (+https://gilisekotong.com)',

    // Perbarui otomatis setelah halaman dibuka bila jeda sudah lewat (cadangan bila cron tidak jalan).
    'auto_refresh' => env('BMKG_AUTO_REFRESH', true),
    'timeout' => 15,

    // Titik acuan: Dermaga Tawun, Desa Sekotong Barat.
    'location' => [
        'name' => 'Dusun Tawun, Sekotong Barat',
        'adm4' => '52.01.07.2002',
        'lat' => -8.7480,
        'lon' => 115.9933,
    ],

    'sources' => [
        'forecast' => [
            'label' => 'Prakiraan Cuaca Sekotong Barat',
            'url' => 'https://api.bmkg.go.id/publik/prakiraan-cuaca?adm4=52.01.07.2002',
            'interval' => 60,   // menit; BMKG memperbarui data ini per jam (cache-control 3600 detik)
        ],
        'nowcast' => [
            'label' => 'Peringatan Dini Cuaca (Nowcast)',
            'url' => 'https://www.bmkg.go.id/alerts/nowcast/id',
            'interval' => 10,
        ],
        'earthquake' => [
            'label' => 'Gempa Bumi Dirasakan',
            'url' => 'https://data.bmkg.go.id/DataMKG/TEWS/gempadirasakan.json',
            'interval' => 10,
        ],
    ],

    // Setelah gagal, tunggu sekian menit sebelum mencoba lagi (hindari membanjiri API yang sedang menolak).
    'retry_after_failure' => 5,

    // Jam operasional perahu (WITA), dipakai untuk merangkum prakiraan per hari.
    'sailing_hours' => [7, 16],

    // Peringatan dini hanya yang menyebut provinsi ini.
    'alert_province' => 'Nusa Tenggara Barat',

    // Kecamatan yang dilewati / dekat rute Tawun–gili: peringatan di sini = TIDAK LAYAK.
    'alert_block_areas' => [
        'SEKOTONG', 'LEMBAR', 'GERUNG', 'KEDIRI', 'KURIPAN', 'LABUAPI',
        'BATU LAYAR', 'BATULAYAR', 'GUNUNG SARI', 'GUNUNGSARI', 'LINGSAR', 'NARMADA', 'LOMBOK BARAT',
    ],
    // Wilayah Lombok lain yang berdekatan: peringatan di sini = WASPADA.
    'alert_caution_areas' => [
        'AMPENAN', 'MATARAM', 'SEKARBELA', 'SELAPARANG', 'CAKRANEGARA', 'SANDUBAYA',
        'PRAYA BARAT', 'PRAYA BARAT DAYA', 'PUJUT', 'JONGGAT', 'LOMBOK TENGAH',
    ],

    // Gempa dianggap "Lombok & sekitarnya" bila jarak pusat gempa ke Tawun ≤ radius ini,
    // atau teks wilayah/dirasakan menyebut salah satu kata kunci.
    'quake_radius_km' => 150,
    'quake_keywords' => ['LOMBOK', 'MATARAM', 'NTB', 'NUSA TENGGARA BARAT', 'SUMBAWA', 'BIMA', 'DOMPU'],
    // Gempa terdekat dalam 24 jam sebelum jam berangkat:
    'quake_caution_magnitude' => 5.0,  // ≥ M5.0 → WASPADA
    'quake_block_magnitude' => 6.0,    // ≥ M6.0 di laut → TIDAK LAYAK sampai ada kepastian dari BMKG
];
