<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('code', 16)->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('boat_id')->constrained()->restrictOnDelete();
            $table->string('trip_type', 20)->default('gili');
            $table->date('trip_date');
            $table->string('departure_time', 5);
            $table->unsignedTinyInteger('passengers');
            $table->string('contact_name');
            $table->string('contact_phone', 20);
            $table->text('notes')->nullable();

            $table->unsignedInteger('trip_price');
            $table->unsignedInteger('meal_total')->default(0);
            $table->unsignedInteger('rental_total')->default(0);
            $table->unsignedInteger('total');

            $table->string('status', 20)->default('menunggu_pembayaran');
            $table->string('payment_proof')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_in_at')->nullable();
            $table->string('admin_note')->nullable();
            $table->timestamps();

            $table->index(['boat_id', 'trip_date']);
        });

        Schema::create('booking_meals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('meal_package_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('qty');
            $table->unsignedInteger('price');
            $table->unsignedInteger('subtotal');
        });

        Schema::create('booking_rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rental_item_id')->constrained()->restrictOnDelete();
            $table->string('name');
            $table->unsignedTinyInteger('qty');
            $table->unsignedInteger('price');
            $table->unsignedInteger('subtotal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('booking_rentals');
        Schema::dropIfExists('booking_meals');
        Schema::dropIfExists('bookings');
    }
};
