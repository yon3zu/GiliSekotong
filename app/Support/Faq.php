<?php

namespace App\Support;

use App\Models\RentalItem;

/** Pertanyaan yang sering muncul: ditampilkan di halaman Kontak dan sebagai structured data FAQPage. */
class Faq
{
    public static function items(): array
    {
        $jacket = rupiah((int) (RentalItem::query()->where('is_active', true)->min('price') ?? 15000));

        return [
            ['Harga Rp350.000 itu per orang atau per perahu?', 'Per perahu. Mau 4, 9, atau 12 orang (maksimal 12 per perahu), harganya tetap Rp350.000.'],
            ['Bisa sewa perahu hanya untuk mancing?', 'Bisa. Pilih "Sewa Mancing" di form pemesanan. Harganya sama dengan trip 3 gili, Rp350.000 per perahu. Alat pancing dan umpan dibawa sendiri.'],
            ['Paket makan dihitung bagaimana?', 'Per paket, dari Kedai Bintang. Isi tiap paket sudah tertera, dan jumlah paketnya Anda tentukan sendiri sesuai selera rombongan.'],
            ['Alat snorkeling disediakan?', 'Alat disewa per pcs: pelampung, kacamata snorkeling, dan fins (sepatu katak) masing-masing '.$jacket.'. Pilih jumlahnya di form pemesanan.'],
            ['Saya belum punya akun, harus daftar dulu?', 'Tidak perlu di awal. Pilih perahu dan atur perjalanan dulu. Saat checkout baru diminta daftar, dan pilihan Anda tetap tersimpan.'],
            ['Bagaimana kalau cuaca buruk?', 'Setiap hari sistem menilai kelayakan berlayar. Jika tanggal trip Anda dinyatakan tidak layak, admin menghubungi Anda untuk memilih tanggal baru tanpa biaya tambahan.'],
            ['Rombongan saya lebih dari kapasitas perahu terbesar.', 'Pesan dua perahu untuk tanggal yang sama, atau hubungi kami lewat WhatsApp untuk dibantu mengatur.'],
            ['Bisa bayar di tempat?', 'Saat ini pembayaran dilakukan lewat QRIS atau transfer bank agar perahu terkunci untuk Anda dan tidak dipesan orang lain.'],
        ];
    }

    public static function schema(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn ($qa) => [
                '@type' => 'Question',
                'name' => $qa[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $qa[1]],
            ], self::items()),
        ];
    }
}
