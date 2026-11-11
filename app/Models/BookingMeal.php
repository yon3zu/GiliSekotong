<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingMeal extends Model
{
    public $timestamps = false;

    protected $fillable = ['meal_package_id', 'name', 'qty', 'price', 'subtotal'];

    public function package(): BelongsTo
    {
        return $this->belongsTo(MealPackage::class, 'meal_package_id');
    }
}
