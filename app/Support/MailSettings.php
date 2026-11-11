<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Contracts\Mail\Mailable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

use function Illuminate\Support\defer;

/** Menerapkan pengaturan SMTP dari panel admin dan mengirim email notifikasi. */
class MailSettings
{
    public static function configured(): bool
    {
        return filled(Setting::get('mail_host')) && filled(Setting::get('mail_from_address'));
    }

    /** Timpa konfigurasi mail Laravel dengan nilai dari database (dipanggil saat aplikasi boot). */
    public static function apply(): void
    {
        if (! self::configured()) {
            return;
        }

        $encryption = Setting::get('mail_encryption');
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.scheme' => $encryption === 'ssl' ? 'smtps' : 'smtp',
            'mail.mailers.smtp.host' => Setting::get('mail_host'),
            'mail.mailers.smtp.port' => (int) Setting::get('mail_port'),
            'mail.mailers.smtp.username' => Setting::get('mail_username') ?: null,
            'mail.mailers.smtp.password' => Setting::get('mail_password') ?: null,
            'mail.mailers.smtp.timeout' => 15,
            'mail.from.address' => Setting::get('mail_from_address'),
            'mail.from.name' => Setting::get('mail_from_name') ?: Setting::get('seo_site_name'),
        ]);
    }

    /**
     * Kirim notifikasi setelah respons terkirim, sehingga pengguna tidak menunggu server SMTP.
     * Kegagalan hanya dicatat di log, tidak menggagalkan pesanan.
     */
    public static function notify(?string $to, Mailable $mail): void
    {
        if (! $to || ! Setting::enabled('mail_enabled') || ! self::configured()) {
            return;
        }

        defer(function () use ($to, $mail) {
            try {
                Mail::to($to)->send($mail);
            } catch (Throwable $e) {
                Log::warning('Email notifikasi gagal dikirim ke '.$to.': '.$e->getMessage());
            }
        });
    }

    public static function adminAddress(): ?string
    {
        return Setting::get('mail_admin_address') ?: Setting::get('contact_email');
    }
}
