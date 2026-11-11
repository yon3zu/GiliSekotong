<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->string('payment_method', 10)->nullable()->after('status'); // transfer | qris
            $table->string('qris_order_id', 60)->nullable()->unique()->after('payment_proof');
            $table->string('qris_status', 20)->nullable()->after('qris_order_id'); // pending | settlement | expire | cancel
            $table->string('qris_url', 500)->nullable()->after('qris_status');
            $table->unsignedInteger('qris_amount')->nullable()->after('qris_url');
            $table->unsignedInteger('qris_fee')->nullable()->after('qris_amount');
            $table->string('qris_fee_payer', 20)->nullable()->after('qris_fee');
            $table->timestamp('qris_created_at')->nullable()->after('qris_fee_payer');

            $table->index('qris_status');
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            $table->dropIndex(['qris_status']);
            $table->dropUnique(['qris_order_id']);
            $table->dropColumn(['payment_method', 'qris_order_id', 'qris_status', 'qris_url', 'qris_amount', 'qris_fee', 'qris_fee_payer', 'qris_created_at']);
        });
    }
};
