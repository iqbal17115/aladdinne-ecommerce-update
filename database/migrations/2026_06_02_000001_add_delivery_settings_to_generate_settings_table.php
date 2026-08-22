<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->boolean('is_weight_charge')->default(false);
            $table->boolean('is_distance_charge')->default(true);
            $table->boolean('is_delivery_free')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->dropColumn('is_weight_charge');
            $table->dropColumn('is_distance_charge');
            $table->dropColumn('is_delivery_free');
        });
    }
};
