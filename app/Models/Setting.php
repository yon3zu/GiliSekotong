<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Throwable;

class Setting extends Model
{
    public const DEFAULTS = [
        'contact_phone' => '0812-0000-0000',
        'contact_whatsapp' => '6281200000000',
        'contact_email' => 'halo@gilisekotong.com',
        'address' => 'Dermaga Tawun, Desa Sekotong Barat, Kec. Sekotong, Lombok Barat, NTB',
        'bank_name' => 'Bank BRI',
        'bank_account' => '0000-01-000000-50-0',
        'bank_holder' => 'GiliSekotong',
        'payment_deadline_hours' => '12',
        'auto_complete_days' => '1',
    ];

    /** Pengaturan SEO, profil bisnis (structured data), dan verifikasi mesin pencari. */
    public const SEO_DEFAULTS = [
        'seo_site_name' => 'GiliSekotong',
        'seo_indexing' => '1',
        'seo_og_image' => '',
        'seo_google_verification' => '',
        'seo_bing_verification' => '',
        'seo_ga4_id' => '',

        'seo_page_home_title' => 'Sewa Perahu Trip 3 Gili Sekotong, Lombok | GiliSekotong',
        'seo_page_home_description' => 'Sewa perahu ke Gili Nanggu, Gili Sudak & Gili Kedis dari Dermaga Tawun, Sekotong. Rp350rb per perahu, maks. 12 orang. Cek cuaca BMKG & pesan online.',
        'seo_page_boats_title' => 'Daftar Perahu Carter ke 3 Gili Sekotong | GiliSekotong',
        'seo_page_boats_description' => 'Pilih perahu katir untuk trip Gili Nanggu, Sudak, Kedis atau sewa mancing di Sekotong. Muat 12 orang, Rp350rb per perahu. Lihat spesifikasi & jadwal.',
        'seo_page_destinations_title' => 'Gili Nanggu, Gili Sudak & Gili Kedis Sekotong | GiliSekotong',
        'seo_page_destinations_description' => 'Panduan tiga gili di Sekotong, Lombok Barat: snorkeling di Gili Nanggu, makan siang di Gili Sudak, foto di Gili Kedis. Berangkat dari Dermaga Tawun.',
        'seo_page_sea_title' => 'Cuaca & Kondisi Laut Sekotong Hari Ini (BMKG) | GiliSekotong',
        'seo_page_sea_description' => 'Prakiraan cuaca BMKG Dusun Tawun, peringatan dini NTB, gempa Lombok, dan status layak berlayar ke Gili Nanggu, Sudak, Kedis untuk 14 hari ke depan.',
        'seo_page_howto_title' => 'Cara Pesan & Harga Sewa Perahu Sekotong | GiliSekotong',
        'seo_page_howto_description' => 'Cara memesan perahu trip 3 gili Sekotong: pilih perahu, tambah paket makan & sewa alat snorkeling, bayar transfer, lalu terima e-tiket QR. Harga lengkap.',
        'seo_page_about_title' => 'Tentang GiliSekotong: Perahu Warga Dusun Tawun',
        'seo_page_about_description' => 'GiliSekotong dikelola bersama pemilik perahu warga Dusun Tawun, Sekotong Barat. Harga per perahu yang jelas dan keputusan berlayar berbasis data BMKG.',
        'seo_page_contact_title' => 'Kontak & FAQ Sewa Perahu Sekotong | GiliSekotong',
        'seo_page_contact_description' => 'Hubungi GiliSekotong lewat WhatsApp untuk sewa perahu ke 3 gili Sekotong. Jawaban seputar harga per perahu, paket makan, sewa alat, dan cuaca.',
        'seo_page_developers_title' => 'Tim Pengembang GiliSekotong | Universitas Bumigora',
        'seo_page_developers_description' => 'Dosen dan mahasiswa Universitas Bumigora yang membangun GiliSekotong, platform pemesanan perahu carter ke tiga gili di Sekotong, Lombok Barat.',

        'biz_type' => 'TravelAgency',
        'biz_street' => 'Dermaga Tawun, Desa Sekotong Barat',
        'biz_locality' => 'Sekotong',
        'biz_region' => 'Nusa Tenggara Barat',
        'biz_postal_code' => '',
        'biz_lat' => '-8.7480',
        'biz_lng' => '115.9933',
        'biz_opening_hours' => 'Mo-Su 07:00-17:00',
        'biz_price_range' => 'Rp350.000 per perahu',
        'biz_maps_url' => '',
        'biz_instagram' => '',
        'biz_facebook' => '',
        'biz_tiktok' => '',
    ];

    /** Pengaturan SMTP untuk email notifikasi. */
    public const MAIL_DEFAULTS = [
        'mail_enabled' => '0',
        'mail_host' => '',
        'mail_port' => '587',
        'mail_encryption' => 'tls',
        'mail_username' => '',
        'mail_password' => '',
        'mail_from_address' => 'halo@gilisekotong.com',
        'mail_from_name' => 'GiliSekotong',
        'mail_admin_address' => '',
    ];

    /** Pembayaran QRIS lewat QRcepat. */
    public const QRIS_DEFAULTS = [
        'qris_enabled' => '0',
        'qris_api_key' => '',
    ];

    /** Disimpan terenkripsi di database (APP_KEY), tidak pernah ditampilkan ulang di form. */
    public const ENCRYPTED = ['mail_password', 'qris_api_key'];

    public $incrementing = false;

    public $timestamps = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('settings', fn () => static::query()->pluck('value', 'key')->all());
        $value = $all[$key] ?? null;

        if ($value !== null && $value !== '' && in_array($key, self::ENCRYPTED, true)) {
            try {
                return Crypt::decryptString($value);
            } catch (Throwable) {
                return null;
            }
        }

        return $value ?? $default ?? (self::DEFAULTS[$key] ?? self::SEO_DEFAULTS[$key] ?? self::MAIL_DEFAULTS[$key] ?? self::QRIS_DEFAULTS[$key] ?? null);
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            if (in_array($key, self::ENCRYPTED, true) && $value !== null && $value !== '') {
                $value = Crypt::encryptString($value);
            }
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('settings');
    }

    public static function enabled(string $key): bool
    {
        return self::get($key) === '1';
    }
}
