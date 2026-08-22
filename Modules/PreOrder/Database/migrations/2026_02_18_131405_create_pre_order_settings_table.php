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
        Schema::create('pre_order_settings', function (Blueprint $table) {
            $table->id();
            $table->boolean('pre_order_active')->default(true);
            $table->boolean('pre_order_for_shop')->default(true);
            $table->float('shop_commission')->default(0);
            $table->string('commission_type')->default('percentage')->comment('percentage, fixed');
            $table->longText('pre_order_policy')->nullable();
            $table->longText('pre_order_refund_policy')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_order_settings');
    }
};
