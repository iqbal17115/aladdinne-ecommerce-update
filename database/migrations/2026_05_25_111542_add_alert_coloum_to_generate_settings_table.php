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
            $table->boolean('low_stock_alert')->default(true);
            $table->boolean('low_stock_mail_notification')->default(0);
            $table->integer('low_stock_alert_quantity')->default(3);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('generate_settings', function (Blueprint $table) {
            $table->dropColumn('low_stock_alert');
            $table->dropColumn('low_stock_mail_notification');
            $table->dropColumn('low_stock_alert_quantity');
        });
    }
};
