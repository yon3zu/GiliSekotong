<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function edit()
    {
        $values = [];
        foreach (array_keys(Setting::DEFAULTS) as $key) {
            $values[$key] = Setting::get($key);
        }

        return view('admin.settings', compact('values'));
    }

    public function update(Request $request)
    {
        Setting::put($request->validate([
            'payment_deadline_hours' => ['required', 'integer', 'min:1', 'max:72'],
            'auto_complete_days' => ['required', 'integer', 'min:1', 'max:30'],
            'bank_name' => ['required', 'string', 'max:60'],
            'bank_account' => ['required', 'string', 'max:40'],
            'bank_holder' => ['required', 'string', 'max:80'],
            'contact_phone' => ['required', 'string', 'max:20'],
            'contact_whatsapp' => ['required', 'regex:/^62[0-9]{8,14}$/'],
            'contact_email' => ['required', 'email'],
            'address' => ['required', 'string', 'max:255'],
        ], ['contact_whatsapp.regex' => 'Format WhatsApp diawali 62, contoh 6281234567890.']));

        return back()->with('success', 'Pengaturan disimpan.');
    }
}
