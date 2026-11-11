<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\QrisPayment;
use Illuminate\Http\Request;
use RuntimeException;

class PaymentController extends Controller
{
    public function edit()
    {
        return view('admin.payment', [
            'enabled' => Setting::enabled('qris_enabled'),
            'hasKey' => filled(Setting::get('qris_api_key')),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate(['qris_api_key' => ['nullable', 'string', 'max:255', 'regex:/^qc_[A-Za-z0-9]+$/']], [
            'qris_api_key.regex' => 'Kunci API QRcepat diawali qc_.',
        ]);

        // Kunci kosong = tetap memakai yang tersimpan, kecuali diminta dihapus.
        $values = ['qris_enabled' => $request->boolean('qris_enabled') ? '1' : '0'];
        if (filled($data['qris_api_key'] ?? null)) {
            $values['qris_api_key'] = $data['qris_api_key'];
        } elseif ($request->boolean('clear_key')) {
            $values['qris_api_key'] = '';
        }
        Setting::put($values);

        return back()->with('success', 'Pengaturan QRIS disimpan.');
    }

    public function test(QrisPayment $qris)
    {
        $key = Setting::get('qris_api_key');
        if (blank($key)) {
            return back()->withErrors(['qris_api_key' => 'Simpan kunci API terlebih dahulu.']);
        }

        try {
            return $qris->testKey($key)
                ? back()->with('success', 'Terhubung ke QRcepat dan kunci API valid. Tidak ada tagihan yang dibuat.')
                : back()->withErrors(['qris_api_key' => 'Kunci API ditolak QRcepat, atau akun sedang disuspend.']);
        } catch (RuntimeException $e) {
            return back()->withErrors(['qris_api_key' => $e->getMessage()]);
        }
    }
}
