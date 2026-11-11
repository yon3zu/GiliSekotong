# Panduan Deploy GiliSekotong ke VPS

Target: VPS **Ubuntu 22.04 / 24.04**, minimal 1 vCPU / 1 GB RAM (disarankan 2 vCPU / 2 GB), domain **gilisekotong.com**.

Isi zip sudah siap jalan: library PHP produksi (`vendor/`) dan tampilan (`public/build/`) sudah termasuk,
jadi di server **tidak perlu** `npm` maupun `composer`. File `.env`, data uji, log, dan unggahan dari laptop **tidak** ikut.

---

## 1. Arahkan domain ke VPS

Di pengelola DNS domain, buat dua record **A** ke IP VPS:

| Nama | Tipe | Nilai |
|---|---|---|
| `@` (gilisekotong.com) | A | IP VPS |
| `www` | A | IP VPS |

Tunggu beberapa menit sampai `ping gilisekotong.com` menunjukkan IP VPS.

## 2. Upload dan ekstrak zip

Dari laptop (Terminal Mac):

```bash
scp gilisekotong-deploy-*.zip root@IP_VPS:/root/
```

Atau pakai FileZilla/WinSCP ke folder `/root`. Lalu di VPS:

```bash
ssh root@IP_VPS
apt-get update && apt-get install -y unzip
mkdir -p /var/www/gilisekotong
unzip /root/gilisekotong-deploy-*.zip -d /var/www/gilisekotong
cd /var/www/gilisekotong
```

## 3. Jalankan skrip setup otomatis

```bash
sudo DOMAIN=gilisekotong.com CERTBOT_EMAIL=email-anda@gmail.com bash deploy/setup-ubuntu.sh
```

Skrip ini memasang dan mengatur: Nginx, MySQL (database + user dengan password acak), PHP 8.3-FPM
(batas unggah 40 MB), ffmpeg (sampul video), file `.env` produksi, migrasi database, data awal,
cron (sinkron BMKG tiap menit dan backup harian 02.00), serta HTTPS Let's Encrypt.
Dengan `CERTBOT_EMAIL`, Anda menyetujui ketentuan layanan Let's Encrypt.

**Catat email & password Super Admin yang tampil di akhir proses**, karena password hanya ditampilkan sekali.
Untuk memakai email admin sendiri, tambahkan `ADMIN_EMAIL=nama@gilisekotong.com` di depan perintah.

Bila DNS belum mengarah ke VPS, tambahkan `SKIP_SSL=1`, lalu pasang HTTPS belakangan:

```bash
certbot --nginx -d gilisekotong.com -d www.gilisekotong.com
```

## 4. Setelah online

1. Buka `https://gilisekotong.com/masuk`, masuk sebagai Super Admin, lalu **ganti password** di menu Profil.
2. **Admin → Pengaturan**: rekening bank, nomor WhatsApp, email, alamat.
3. **Admin → Perahu / Paket Makan / Sewa Alat**: sesuaikan dengan data asli, unggah foto perahu.
4. **Admin → Pemilik Kapal**: daftarkan pemilik kapal asli.
5. **Admin → Galeri Beranda**: unggah foto/video keindahan gili.
6. **Admin → Email (SMTP)**: isi akun email, tekan *Kirim email uji*, lalu aktifkan notifikasi.
7. **Admin → SEO**: lengkapi kode pos & koordinat dermaga, lalu daftarkan situs di
   [Google Search Console](https://search.google.com/search-console), tempel kode verifikasi, dan kirim `https://gilisekotong.com/sitemap.xml`.
8. **Admin → Data Latih**: ganti data latih contoh dengan catatan pelayaran asli, lalu latih ulang model.

Firewall (opsional, pastikan port SSH Anda benar sebelum mengaktifkan):

```bash
ufw allow OpenSSH && ufw allow 'Nginx Full' && ufw enable
```

---

## Memperbarui aplikasi (versi baru)

`.env` dan file unggahan tidak ada di dalam zip, jadi aman ditimpa. Skrip update hanya membackup,
memigrasi database, dan menyegarkan cache, tanpa menyentuh Nginx, HTTPS, maupun versi PHP:

```bash
cd /var/www/gilisekotong
sudo -u www-data php artisan down
unzip -o /root/gilisekotong-update-BARU.zip -d /var/www/gilisekotong
sudo bash deploy/update.sh
```

Jangan memakai `deploy/setup-ubuntu.sh` untuk update; skrip itu untuk pemasangan pertama.

Setelah mengubah `.env` secara manual, jalankan `sudo -u www-data php artisan optimize`.

## Backup

- Otomatis setiap hari 02.00 ke `/var/backups/gilisekotong` (database + foto/bukti transfer), disimpan 14 hari.
- Backup manual kapan saja: `sudo bash /var/www/gilisekotong/deploy/backup.sh`
- Unduh sesekali ke laptop agar aman bila VPS rusak:
  `scp root@IP_VPS:/var/backups/gilisekotong/db-*.sql.gz .`

## (Opsional) Memindahkan data dari laptop

Zip berisi aplikasi kosong. Bila ingin membawa data dari XAMPP (akun, pesanan, pengaturan, foto), di Mac:

```bash
/Applications/XAMPP/xamppfiles/bin/mysqldump -uroot eticket_sekotong > gilisekotong-data.sql
tar -czf gilisekotong-uploads.tar.gz -C /Applications/XAMPP/xamppfiles/htdocs/E-TIcket/storage/app public private
scp gilisekotong-data.sql gilisekotong-uploads.tar.gz root@IP_VPS:/root/
```

Di VPS, **setelah** skrip setup selesai:

```bash
cd /var/www/gilisekotong
mysql gilisekotong < /root/gilisekotong-data.sql          # menimpa data awal produksi
tar -xzf /root/gilisekotong-uploads.tar.gz -C storage/app
chown -R www-data:www-data storage
sudo -u www-data php artisan migrate --force && sudo -u www-data php artisan optimize
```

Catatan:
- Akun yang berlaku menjadi akun dari laptop (mis. `superadmin@gilisekotong.com` dengan password lamanya). Segera ganti password.
- Password SMTP tersimpan terenkripsi dengan APP_KEY laptop, jadi isi ulang di Admin → Email (SMTP).
- Data laptop masih berisi akun & pesanan uji coba. Untuk peluncuran resmi, lebih bersih memulai dari data awal produksi.

## Pemecahan masalah

| Gejala | Solusi |
|---|---|
| Halaman 500 | `tail -50 /var/www/gilisekotong/storage/logs/laravel-*.log` |
| Foto/galeri tidak tampil | `sudo -u www-data php artisan storage:link` lalu cek izin folder `storage` |
| Unggahan video ditolak | Pastikan `client_max_body_size 40M` (Nginx) dan `upload_max_filesize = 40M` (PHP), lalu `systemctl reload nginx php8.3-fpm` |
| Data BMKG tidak terbarui | Cek cron: `cat /etc/cron.d/gilisekotong`; jalankan `sudo -u www-data php artisan bmkg:sync` |
| Email tidak terkirim | Admin → Email (SMTP) → Kirim email uji; lihat pesan errornya |
