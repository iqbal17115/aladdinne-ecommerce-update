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
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_preorder')->default(false)->index();
            $table->string('expected_delivery_date')->nullable();
            $table->boolean('is_available')->default(true);
            $table->boolean('is_prepay')->default(false);
            $table->decimal('prepay_amount', 12, 2)->default(0.00)->nullable();
            $table->unsignedInteger('preorder_quantity_limit')->default(1)->nullable();
            $table->text('preorder_notice')->nullable();
            $table->boolean('is_refund')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_preorder');
            $table->dropColumn('expected_delivery_date');
            $table->dropColumn('is_available');
            $table->dropColumn('is_prepay');
            $table->dropColumn('prepay_amount');
            $table->dropColumn('preorder_quantity_limit');
            $table->dropColumn('preorder_notice');
            $table->dropColumn('is_refund');
        });
    }
};
