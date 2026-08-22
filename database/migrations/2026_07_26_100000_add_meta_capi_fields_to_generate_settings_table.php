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
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->boolean('meta_pixel_enabled')->default(false)->after('meta_pixel_id');
            $table->text('meta_capi_access_token')->nullable()->after('meta_pixel_enabled');
            $table->string('meta_test_event_code', 64)->nullable()->after('meta_capi_access_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->dropColumn([
                'meta_pixel_enabled',
                'meta_capi_access_token',
                'meta_test_event_code',
            ]);
        });
    }
};
