<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->float('distance')->default(0)->nullable();
            $table->double('latitude')->nullable();
            $table->double('longitude')->nullable();
            $table->double('price_per_km')->nullable();
            $table->double('price_per_min')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            $table->dropColumn('distance');
            $table->dropColumn('latitude');
            $table->dropColumn('longitude');
            $table->dropColumn('price_per_km');
            $table->dropColumn('price_per_min');
        });
    }
};
