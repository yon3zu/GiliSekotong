<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookingRental extends Model
{
    public $timestamps = false;

    protected $fillable = ['rental_item_id', 'name', 'qty', 'price', 'subtotal'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(RentalItem::class, 'rental_item_id');
    }
}
