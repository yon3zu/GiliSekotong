<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\TestMail;
use App\Models\Setting;
use App\Support\MailSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

class EmailController extends Controller
{
    public function edit()
    {
        $values = collect(array_keys(Setting::MAIL_DEFAULTS))->mapWithKeys(fn ($k) => [$k => Setting::get($k)]);

        return view('admin.email', [
            'values' => $values->except('mail_password'),
            'hasPassword' => filled($values['mail_password']),
            'configured' => MailSettings::configured(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'mail_host' => ['nullable', 'string', 'max:120'],
            'mail_port' => ['required', 'integer', 'between:1,65535'],
            'mail_encryption' => ['required', Rule::in(['tls', 'ssl', 'none'])],
            'mail_username' => ['nullable', 'string', 'max:150'],
            'mail_password' => ['nullable', 'string', 'max:255'],
            'mail_from_address' => ['required', 'email', 'max:150'],
            'mail_from_name' => ['required', 'string', 'max:80'],
            'mail_admin_address' => ['nullable', 'email', 'max:150'],
        ]);

        // Password kosong = tetap memakai yang tersimpan, kecuali diminta dihapus.
        if (blank($data['mail_password'] ?? null)) {
            unset($data['mail_password']);
        }
        if ($request->boolean('clear_password')) {
            $data['mail_password'] = '';
        }
        $data['mail_enabled'] = $request->boolean('mail_enabled') ? '1' : '0';

        Setting::put(array_map(fn ($v) => $v ?? '', $data));

        return back()->with('success', 'Pengaturan email disimpan. Kirim email uji untuk memastikan SMTP berjalan.');
    }

    public function test(Request $request)
    {
        $data = $request->validate(['test_to' => ['required', 'email']]);

        if (! MailSettings::configured()) {
            return back()->withErrors(['test_to' => 'Isi dan simpan host SMTP serta alamat pengirim terlebih dahulu.']);
        }

        MailSettings::apply();
        app('mail.manager')->purge('smtp');

        try {
            Mail::to($data['test_to'])->send(new TestMail);
        } catch (Throwable $e) {
            return back()->withInput()->withErrors(['test_to' => 'Gagal mengirim: '.mb_strimwidth($e->getMessage(), 0, 300, '…')]);
        }

        return back()->withInput()->with('success', "Email uji terkirim ke {$data['test_to']}. Periksa kotak masuk atau folder spam.");
    }
}
