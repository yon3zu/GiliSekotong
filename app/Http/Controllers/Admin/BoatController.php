<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Boat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BoatController extends Controller
{
    public function index()
    {
        return view('admin.boats.index', ['boats' => Boat::query()->with('owner')->withCount('bookings')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.boats.form', ['boat' => new Boat(['capacity' => Boat::MAX_CAPACITY, 'trip_price' => 350000, 'is_active' => true, 'type' => 'Perahu Katir'])]);
    }

    public function store(Request $request)
    {
        Boat::create($this->validated($request));

        return redirect()->route('admin.boats.index')->with('success', 'Perahu ditambahkan.');
    }

    public function edit(Boat $boat)
    {
        return view('admin.boats.form', compact('boat'));
    }

    public function update(Request $request, Boat $boat)
    {
        $boat->update($this->validated($request, $boat));

        return redirect()->route('admin.boats.index')->with('success', 'Data perahu diperbarui.');
    }

    public function destroy(Boat $boat)
    {
        if ($boat->bookings()->exists()) {
            $boat->update(['is_active' => false]);

            return back()->with('success', 'Perahu memiliki riwayat pesanan, jadi hanya dinonaktifkan.');
        }

        if ($boat->photo) {
            Storage::disk('public')->delete($boat->photo);
        }
        $boat->delete();

        return back()->with('success', 'Perahu dihapus.');
    }

    private function validated(Request $request, ?Boat $boat = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'owner_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'pemilik')],
            'type' => ['required', 'string', 'max:50'],
            'capacity' => ['required', 'integer', 'min:1', 'max:'.Boat::MAX_CAPACITY],
            'length_m' => ['nullable', 'numeric', 'min:1', 'max:40'],
            'engine' => ['nullable', 'string', 'max:80'],
            'captain' => ['nullable', 'string', 'max:80'],
            'built_year' => ['nullable', 'integer', 'min:1980', 'max:'.date('Y')],
            'facilities' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string', 'max:2000'],
            'trip_price' => ['required', 'integer', 'min:0'],
            'photo' => ['nullable', 'image', 'max:4096'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['facilities'] = array_values(array_filter(array_map('trim', explode("\n", $data['facilities'] ?? ''))));
        $data['is_active'] = $request->boolean('is_active');
        $data['slug'] = $boat?->slug ?? $this->uniqueSlug($data['name']);

        if ($request->hasFile('photo')) {
            if ($boat?->photo) {
                Storage::disk('public')->delete($boat->photo);
            }
            $data['photo'] = $request->file('photo')->store('boats', 'public');
        } else {
            unset($data['photo']);
        }

        return $data;
    }

    private function uniqueSlug(string $name): string
    {
        $slug = $base = Str::slug($name);
        for ($i = 2; Boat::query()->where('slug', $slug)->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
