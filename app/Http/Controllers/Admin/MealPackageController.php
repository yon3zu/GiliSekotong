<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BookingMeal;
use App\Models\MealPackage;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MealPackageController extends Controller
{
    public function index()
    {
        return view('admin.meals.index', ['meals' => MealPackage::query()->orderBy('code')->get()]);
    }

    public function create()
    {
        return view('admin.meals.form', ['meal' => new MealPackage(['is_active' => true])]);
    }

    public function store(Request $request)
    {
        MealPackage::create($this->validated($request));

        return redirect()->route('admin.meals.index')->with('success', 'Paket makan ditambahkan.');
    }

    public function edit(MealPackage $meal)
    {
        return view('admin.meals.form', compact('meal'));
    }

    public function update(Request $request, MealPackage $meal)
    {
        $meal->update($this->validated($request, $meal));

        return redirect()->route('admin.meals.index')->with('success', 'Paket makan diperbarui.');
    }

    public function destroy(MealPackage $meal)
    {
        if (BookingMeal::query()->where('meal_package_id', $meal->id)->exists()) {
            $meal->update(['is_active' => false]);

            return back()->with('success', 'Paket sudah pernah dipesan, jadi hanya dinonaktifkan.');
        }
        $meal->delete();

        return back()->with('success', 'Paket makan dihapus.');
    }

    private function validated(Request $request, ?MealPackage $meal = null): array
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:10', Rule::unique('meal_packages')->ignore($meal?->id)],
            'name' => ['required', 'string', 'max:60'],
            'menu' => ['required', 'string', 'max:800'],
            'price' => ['required', 'integer', 'min:0'],
        ]);

        $data['menu'] = array_values(array_filter(array_map('trim', explode("\n", $data['menu']))));
        $data['is_active'] = $request->boolean('is_active');

        return $data;
    }
}
