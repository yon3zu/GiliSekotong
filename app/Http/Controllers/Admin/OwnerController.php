<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

/** Pendaftaran akun pemilik kapal. Pemilik tidak bisa mendaftar sendiri. */
class OwnerController extends Controller
{
    public function index()
    {
        return view('admin.owners.index', [
            'owners' => User::query()->where('role', 'pemilik')->with('boats')->orderBy('name')->get(),
            'unowned' => Boat::query()->whereNull('owner_id')->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.owners.form', ['owner' => new User(['is_active' => true]), 'boats' => Boat::orderBy('name')->get()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        DB::transaction(function () use ($data) {
            $owner = User::create([...$data['user'], 'role' => 'pemilik']);
            $this->assignBoats($owner, $data['boats']);
        });

        return redirect()->route('admin.owners.index')
            ->with('success', "Akun pemilik dibuat. Login dengan nomor HP {$data['user']['phone']} dan kata sandi yang tadi diisi.");
    }

    public function edit(User $owner)
    {
        abort_unless($owner->isOwner(), 404);

        return view('admin.owners.form', ['owner' => $owner, 'boats' => Boat::orderBy('name')->get()]);
    }

    public function update(Request $request, User $owner)
    {
        abort_unless($owner->isOwner(), 404);
        $data = $this->validated($request, $owner);
        DB::transaction(function () use ($owner, $data) {
            $owner->update($data['user']);
            $this->assignBoats($owner, $data['boats']);
        });

        return redirect()->route('admin.owners.index')->with('success', 'Data pemilik diperbarui.');
    }

    private function validated(Request $request, ?User $owner = null): array
    {
        $request->merge(['phone' => User::normalizePhone($request->input('phone'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^0[0-9]{8,14}$/', Rule::unique('users', 'phone')->where('role', 'pemilik')->ignore($owner?->id)],
            'email' => ['nullable', 'email', 'max:150', Rule::unique('users', 'email')->ignore($owner?->id)],
            'password' => [$owner ? 'nullable' : 'required', Password::min(6)],
            'boats' => ['nullable', 'array'],
            'boats.*' => ['integer', 'exists:boats,id'],
        ], [
            'phone.regex' => 'Nomor HP harus diawali 0, contoh 081234567890.',
            'phone.unique' => 'Nomor HP ini sudah dipakai pemilik lain.',
            'password.min' => 'Kata sandi minimal 6 karakter. Pilih yang mudah diingat pemilik.',
        ]);

        $user = collect($data)->only(['name', 'phone', 'email', 'password'])->filter(fn ($v, $k) => $k !== 'password' || filled($v))->all();
        $user['is_active'] = $request->boolean('is_active', true);

        return ['user' => $user, 'boats' => $data['boats'] ?? []];
    }

    private function assignBoats(User $owner, array $boatIds): void
    {
        Boat::query()->where('owner_id', $owner->id)->whereNotIn('id', $boatIds)->update(['owner_id' => null]);
        Boat::query()->whereIn('id', $boatIds)->update(['owner_id' => $owner->id]);
    }
}
