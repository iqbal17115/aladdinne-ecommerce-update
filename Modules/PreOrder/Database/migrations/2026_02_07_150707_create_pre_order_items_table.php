<?php

use App\Models\Product;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Modules\PreOrder\App\Models\PreOrder;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pre_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PreOrder::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Product::class)->nullable()->constrained()->nullOnDelete();
            $table->string('product_name');
            $table->float('product_price')->default(0);
            $table->float('buying_price')->default(0);
            $table->float('price')->default(0);
            $table->float('discount')->nullable()->default(0);
            $table->integer('quantity');
            $table->string('unit')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_order_items');
    }
};
