# GiliSekotong — E-Ticketing Perahu Trip 3 Gili (gilisekotong.com)

Platform pemesanan perahu cateran dari Dermaga Tawun, Sekotong (Lombok Barat) ke Gili Nanggu, Gili Sudak, dan Gili Kedis,
dengan **sistem pendukung keputusan pelayaran berbasis pohon keputusan (C4.5 / ID3)**.

Stack: Laravel 12 · MySQL (MariaDB XAMPP) · Tailwind CSS 4 · Alpine.js · PHP 8.2 dari XAMPP.

## Menjalankan

```bash
# dari folder proyek, pakai PHP bawaan XAMPP
PHP=/Applications/XAMPP/xamppfiles/bin/php
$PHP /opt/homebrew/bin/composer install
cp .env.example .env && $PHP artisan key:generate   # lewati bila .env sudah ada
# buat database `eticket_sekotong` (utf8mb4) di phpMyAdmin, lalu:
$PHP artisan migrate:fresh --seed
$PHP artisan storage:link
npm install && npm run build
chmod -R ugo+rwX storage bootstrap/cache            # Apache XAMPP berjalan sebagai user daemon
$PHP artisan bmkg:sync                               # isi data BMKG pertama kali
$PHP artisan schedule:work                           # opsional: biarkan jalan agar BMKG tersinkron berkala
```

Buka **http://localhost/E-TIcket/public** (Apache XAMPP), atau `$PHP artisan serve` lalu http://127.0.0.1:8000.

### Akun awal (dari seeder — ganti kata sandinya)

| Peran | Email | Sandi |
|---|---|---|
| Super Admin | superadmin@gilisekotong.com | password |
| Admin | admin@gilisekotong.com | password |
| Pemesan | pemesan@contoh.id | password |
| Pemilik Kapal (login pakai no. HP) | 081300000001 · 081300000002 · 081300000003 | kapal123 |

## Hak akses

- **Pemilik Kapal** — akun hanya dibuat admin (menu *Pemilik Kapal*), login dengan nomor HP. Halaman `/pemilik` berhuruf besar: jadwal kapalnya 14 hari, **lapor tamu offline** (tanggal langsung tertutup untuk pemesanan online), pembukuan bulanan (sewa kapal online + offline), riwayat penumpang, dan panduan yang bisa dicetak (`/pemilik/panduan`).
- **Pemesan** — pesan perahu, unggah bukti transfer, lihat/cetak e-tiket QR, batalkan pesanan yang belum dibayar.
- **Admin** — mendaftarkan pemilik kapal, melihat & membuka kembali laporan tamu offline (menu *Tamu Offline*), verifikasi pembayaran, check-in (pindai QR), jadwal ulang, input kondisi laut harian, lihat pohon keputusan, laporan.
- **Super Admin** — semua menu admin + data latih, melatih ulang model, master perahu/paket makan/pengguna, pengaturan.

## Aturan harga

- Sewa perahu **Rp350.000 per perahu** (bukan per orang), maksimal **12 orang** per perahu. Pilih **Trip 3 Gili** atau **Sewa Mancing**, harganya sama. Diatur per perahu di menu Perahu.
- Paket makan dari **Kedai Bintang**, dihitung **per paket** (tidak dikaitkan dengan jumlah orang): Paket Udang 110rb, Paket Cumi 95rb, Paket Ikan Bakar 85rb, Paket Hemat 75rb. Menu Paket Makan.
- **Sewa alat per pcs**: pelampung, kacamata snorkeling, fins (sepatu katak) masing-masing Rp15.000. Tiap alat maksimal sejumlah penumpang. Menu Sewa Alat.
- Satu perahu = satu rombongan per tanggal (tidak bisa dipesan ganda).

## Galeri beranda (Admin → Galeri Beranda)

- Admin dan Super Admin mengunggah foto (JPG/PNG, maks. 8 MB) atau video pendek (MP4/WebM/MOV, maks. 35 MB) keindahan gili.
- **Hero**: tampil layar penuh paling atas; beberapa foto bergantian otomatis, video diputar tanpa suara (di HP dan mode hemat data hanya gambar sampul yang dimuat).
- **Galeri**: mosaik tanpa celah di bawah hero, klik untuk memperbesar foto atau memutar video.
- Foto otomatis diperkecil ke 2000 px (JPEG progresif) dan data EXIF/GPS dibuang. Sampul video dibuat otomatis bila `ffmpeg` terpasang di server, atau diunggah manual.
- Selama belum ada media, beranda memakai ilustrasi tiga gili. Foto hero pertama juga dipakai sebagai gambar berbagi (Open Graph) bila belum diatur di menu SEO.
- Di server, pastikan `client_max_body_size 40M;` (Nginx) dan `upload_max_filesize`/`post_max_size` PHP minimal 40M.

## SEO (Admin → SEO)

- Judul & deskripsi per halaman (dengan penghitung karakter dan pratinjau hasil Google), gambar berbagi Open Graph, verifikasi Google Search Console/Bing, Google Analytics 4, dan profil bisnis lokal.
- Otomatis: `/sitemap.xml`, `/robots.txt` (admin, pemilik, pesanan, checkout tidak diindeks), tag canonical, Open Graph/Twitter, structured data schema.org (TravelAgency, WebSite, Product + harga per perahu, BreadcrumbList, FAQPage, TouristAttraction).
- Setelah online di https://gilisekotong.com: ubah `APP_URL` di `.env`, daftar Google Search Console lalu kirim sitemap, dan buat Google Business Profile di titik Dermaga Tawun.

## Email (Admin → Email (SMTP))

- Host, port, enkripsi, username, password (disimpan terenkripsi dengan APP_KEY), alamat & nama pengirim, email admin, dan tombol kirim email uji.
- Notifikasi otomatis bila diaktifkan: instruksi pembayaran saat pesanan dibuat, e-tiket saat pembayaran diverifikasi, dan pemberitahuan ke admin saat ada bukti transfer baru. Email dikirim setelah halaman selesai dimuat, dan kegagalan hanya dicatat di log.

## Integrasi BMKG

| Sumber | Endpoint | Jeda minimum | Dipakai untuk |
|---|---|---|---|
| Prakiraan cuaca Dusun Tawun (adm4 52.01.07.2002) | `api.bmkg.go.id/publik/prakiraan-cuaca` | 60 menit | Input pohon keputusan: cuaca, angin, jarak pandang, perkiraan gelombang |
| Peringatan dini cuaca (nowcast) | `bmkg.go.id/alerts/nowcast/id` | 10 menit | Hanya item yang menyebut **Nusa Tenggara Barat**; kecamatan sekitar rute → Tidak Layak, Lombok lain → Waspada |
| Gempa dirasakan (15 terbaru) | `data.bmkg.go.id/DataMKG/TEWS/gempadirasakan.json` | 10 menit | Hanya ≤150 km dari Tawun / menyebut Lombok-NTB; ≥M5 → Waspada, ≥M6 di laut → Tidak Layak (24 jam) |

- **Tidak ada request ke BMKG saat pengunjung membuka halaman.** Data diambil oleh `php artisan bmkg:sync` (terjadwal tiap 10 menit lewat `schedule:work`/cron), tombol *Perbarui sekarang* di **Admin → Data BMKG**, atau otomatis *setelah* respons halaman terkirim bila jeda minimum sudah lewat. Lock cache mencegah sinkron ganda; setelah gagal (mis. HTTP 429) sistem menunggu 5 menit.
- Keputusan akhir = keputusan nakhoda bila ada, selain itu **yang terberat** antara prediksi pohon keputusan dan aturan BMKG. Aturan BMKG hanya bisa memperberat, tidak pernah melonggarkan.
- Data yang diinput petugas tidak ditimpa BMKG (bisa dikembalikan lewat tombol *Pakai data BMKG*).
- API prakiraan cuaca tidak memuat tinggi gelombang, sehingga diperkirakan dari kecepatan angin (tinggi gelombang tipikal skala Beaufort, cenderung konservatif).
- Semua ambang (radius gempa, magnitudo, daftar kecamatan, jeda) ada di `config/bmkg.php`.

## Draf pesanan tidak hilang saat daftar

Pilihan pemesan disimpan di sesi (`booking_draft`) sebelum login. Halaman checkout menampilkan form daftar/masuk
langsung di samping ringkasan; `session()->regenerate()` setelah login/daftar mempertahankan isi sesi, sehingga pemesan
kembali ke checkout dengan pilihan utuh. Lihat `app/Http/Controllers/BookingController.php` dan `AuthController.php`.

## Pohon keputusan (inti skripsi)

| Berkas | Isi |
|---|---|
| `app/Services/DecisionTree.php` | Entropy, Information Gain, Split Info, Gain Ratio, pembentukan pohon ID3/C4.5, prediksi, aturan IF–THEN, confusion matrix, stratified split, k-fold CV |
| `app/Services/SailingModelTrainer.php` | Melatih dari tabel `sailing_records`, menyimpan model ke `decision_tree_models`, memprediksi ulang kondisi laut ke depan |
| `app/Support/Sailing.php` | Atribut & kategori (cuaca, angin, gelombang, jarak pandang, pasang surut) + diskretisasi knot/meter |
| `resources/views/admin/tree/index.blade.php` | Visualisasi pohon, tabel perhitungan simpul akar, confusion matrix, precision/recall/F1, aturan, uji coba prediksi |

Alur: petugas input kondisi laut harian → model aktif memprediksi **Layak / Waspada / Tidak Layak** → admin bisa menetapkan
keputusan akhir berbeda (catatan nakhoda) → tanggal *Tidak Layak* otomatis tertutup untuk pemesanan dan pesanan yang
terdampak ditandai untuk dijadwalkan ulang. Hasilnya tampil publik di halaman **Kondisi Laut**.

> ⚠️ **Data latih bawaan adalah data contoh (sintetis)**, dibuat `SailingRecordSeeder` agar sistem bisa diuji.
> Untuk penelitian, ganti dengan catatan pelayaran asli (nakhoda/syahbandar + BMKG Maritim) lewat
> **Admin → Data Latih → Impor CSV**, lalu latih ulang. Format CSV ada di halaman tersebut, contoh bisa diunduh lewat Ekspor.

Eksperimen pada data contoh (split 80/20, seed 42):

| Algoritma | Min. sampel | Akurasi uji | 5-fold CV | Jumlah aturan |
|---|---|---|---|---|
| C4.5 | 2 | 81,25% | 86,67% | 51 |
| C4.5 | 10 | 85,42% | 88,75% | 37 |
| C4.5 | 15 | 83,33% | 90,00% | 30 |
| ID3 | 10 | 83,33% | 88,75% | 39 |

## Pengujian

```bash
/Applications/XAMPP/xamppfiles/bin/php artisan test
```

Mencakup validasi perhitungan entropy/gain terhadap dataset *Play Tennis* (Quinlan), alur draf → daftar → checkout,
harga per grup (gili & mancing), batas sewa alat per penumpang, blokir tanggal tidak layak, pesan ganda, hak akses peran, unggah bukti → verifikasi → e-tiket → check-in.

## Yang perlu diganti sebelum dipakai sungguhan

- Nomor rekening, WhatsApp, email, alamat (menu **Pengaturan** — sekarang berisi placeholder).
- Foto perahu (menu **Perahu**; tanpa foto ditampilkan ilustrasi).
- Nama nakhoda & spesifikasi perahu di seeder adalah contoh.
