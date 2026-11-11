<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catatan sinkronisasi per sumber, sekaligus pengatur jeda request ke BMKG.
        Schema::create('bmkg_syncs', function (Blueprint $table) {
            $table->string('source', 20)->primary();
            $table->timestamp('last_attempt_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->string('status', 10)->nullable();
            $table->string('message')->nullable();
            $table->unsignedInteger('items')->default(0);
        });

        Schema::create('bmkg_forecasts', function (Blueprint $table) {
            $table->id();
            $table->dateTime('local_datetime')->unique();
            $table->dateTime('utc_datetime');
            $table->smallInteger('weather_code');
            $table->string('weather_desc', 40);
            $table->decimal('temperature', 4, 1);
            $table->unsignedTinyInteger('humidity');
            $table->unsignedTinyInteger('cloud_cover');
            $table->decimal('precipitation', 5, 1)->default(0);
            $table->decimal('wind_kmh', 5, 1);
            $table->string('wind_dir', 4)->nullable();
            $table->unsignedSmallInteger('wind_deg')->nullable();
            $table->string('visibility', 20)->nullable();
            $table->dateTime('analysis_date')->nullable();
            $table->timestamps();
        });

        Schema::create('bmkg_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('guid')->unique();
            $table->string('title');
            $table->text('description');
            $table->string('link')->nullable();
            $table->text('areas')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('published_at')->nullable();
            $table->string('impact', 20)->default('info'); // info | waspada | tidak_layak
            $table->timestamps();
        });

        Schema::create('bmkg_earthquakes', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->dateTime('occurred_at');
            $table->decimal('magnitude', 3, 1);
            $table->unsignedSmallInteger('depth_km')->nullable();
            $table->decimal('lat', 6, 2);
            $table->decimal('lon', 6, 2);
            $table->string('region');
            $table->text('felt')->nullable();
            $table->unsignedSmallInteger('distance_km');
            $table->boolean('is_nearby')->default(false);
            $table->boolean('at_sea')->default(false);
            $table->timestamps();
        });

        Schema::table('sea_conditions', function (Blueprint $table) {
            $table->string('source', 10)->default('manual')->after('date');
            $table->string('bmkg_flag', 20)->nullable()->after('prediction');
            $table->json('bmkg_reasons')->nullable()->after('bmkg_flag');
        });
    }

    public function down(): void
    {
        Schema::table('sea_conditions', function (Blueprint $table) {
            $table->dropColumn(['source', 'bmkg_flag', 'bmkg_reasons']);
        });
        Schema::dropIfExists('bmkg_earthquakes');
        Schema::dropIfExists('bmkg_alerts');
        Schema::dropIfExists('bmkg_forecasts');
        Schema::dropIfExists('bmkg_syncs');
    }
};
