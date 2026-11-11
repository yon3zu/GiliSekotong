<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    /** Masuk dengan email, atau nomor HP (dipakai pemilik kapal yang tidak punya email). */
    public function login(Request $request)
    {
        $data = $request->validate([
            'login' => ['required', 'string', 'max:150'],
            'password' => ['required'],
        ], ['login.required' => 'Isi email atau nomor HP.']);

        $login = trim($data['login']);
        $candidates = str_contains($login, '@')
            ? User::query()->where('email', $login)->get()
            : User::query()->whereIn('phone', array_filter([$login, User::normalizePhone($login)]))->get();

        $user = $candidates->first(fn (User $u) => $u->is_active && Hash::check($data['password'], $u->password));

        if (! $user) {
            return back()->withErrors(['login' => 'Email/nomor HP atau kata sandi tidak cocok.'])->onlyInput('login');
        }

        Auth::login($user, $request->boolean('remember'));
        // regenerate() mempertahankan isi sesi, jadi draf pemesanan tetap ada.
        $request->session()->regenerate();

        return $this->afterAuth($request);
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone' => ['required', 'string', 'regex:/^[0-9+\-\s]{9,20}$/'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'email.unique' => 'Email ini sudah terdaftar. Silakan masuk.',
            'phone.regex' => 'Nomor WhatsApp hanya boleh berisi angka.',
        ]);

        $user = User::create([...$data, 'role' => 'pemesan']);

        Auth::login($user);
        $request->session()->regenerate();

        return $this->afterAuth($request, 'Akun berhasil dibuat.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    private function afterAuth(Request $request, ?string $message = null)
    {
        $user = $request->user();

        if ($user->isStaff() || $user->isOwner()) {
            return redirect()->intended($user->homeRoute());
        }

        if ($request->session()->has('booking_draft')) {
            return redirect()->route('booking.checkout')
                ->with('success', trim(($message ?? 'Selamat datang kembali.').' Pesanan Anda masih tersimpan, tinggal konfirmasi.'));
        }

        return redirect()->intended(route('orders.index'))->with('success', $message);
    }
}
