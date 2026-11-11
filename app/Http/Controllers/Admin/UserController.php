<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::query()
            ->withCount('bookings')
            ->when($request->query('role'), fn ($q, $r) => $q->where('role', $r))
            ->when($request->query('q'), fn ($q, $t) => $q->where(fn ($w) => $w->where('name', 'like', "%{$t}%")->orWhere('email', 'like', "%{$t}%")))
            ->orderByRaw("CASE role WHEN 'superadmin' THEN 0 WHEN 'admin' THEN 1 WHEN 'pemilik' THEN 2 ELSE 3 END")
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.form', ['user' => new User(['role' => 'admin', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('success', 'Pengguna ditambahkan.');
    }

    public function edit(User $user)
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if ($user->is($request->user())) {
            // Cegah superadmin mengunci dirinya sendiri.
            $data['role'] = $user->role;
            $data['is_active'] = true;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'Data pengguna diperbarui.');
    }

    public function destroy(Request $request, User $user)
    {
        abort_if($user->is($request->user()), 422, 'Tidak bisa menghapus akun sendiri.');

        if ($user->bookings()->exists()) {
            $user->update(['is_active' => false]);

            return back()->with('success', 'Pengguna punya riwayat pesanan, jadi hanya dinonaktifkan.');
        }
        $user->delete();

        return back()->with('success', 'Pengguna dihapus.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => [Rule::requiredIf($request->input('role') !== 'pemilik'), 'nullable', 'email', 'max:150', Rule::unique('users')->ignore($user?->id)],
            'phone' => [Rule::requiredIf($request->input('role') === 'pemilik'), 'nullable', 'string', 'max:20'],
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'password' => [$user ? 'nullable' : 'required', Password::min(8)],
        ]);

        $data['is_active'] = $request->boolean('is_active');
        if ($data['role'] === 'pemilik') {
            $data['phone'] = User::normalizePhone($data['phone']);
        }
        if (empty($data['password'])) {
            unset($data['password']);
        }

        return $data;
    }
}
