<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingRental;
use App\Models\RentalItem;
use Illuminate\Http\Request;

class RentalItemController extends Controller
{
    public function index()
    {
        return view('admin.rentals.index', ['items' => RentalItem::query()->orderBy('sort')->get()]);
    }

    public function create()
    {
        return view('admin.rentals.form', ['item' => new RentalItem(['price' => 15000, 'is_active' => true, 'sort' => RentalItem::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        RentalItem::create($this->validated($request));

        return redirect()->route('admin.rentals.index')->with('success', 'Alat sewa ditambahkan.');
    }

    public function edit(RentalItem $rental)
    {
        return view('admin.rentals.form', ['item' => $rental]);
    }

    public function update(Request $request, RentalItem $rental)
    {
        $rental->update($this->validated($request));

        return redirect()->route('admin.rentals.index')->with('success', 'Alat sewa diperbarui.');
    }

    public function destroy(RentalItem $rental)
    {
        if (BookingRental::query()->where('rental_item_id', $rental->id)->exists()) {
            $rental->update(['is_active' => false]);

            return back()->with('success', 'Alat sudah pernah disewa, jadi hanya dinonaktifkan.');
        }
        $rental->delete();

        return back()->with('success', 'Alat sewa dihapus.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:160'],
            'price' => ['required', 'integer', 'min:0'],
            'sort' => ['required', 'integer', 'min:0', 'max:99'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
