<?php

use App\Models\Order;
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
        Schema::create('courier_trackings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Order::class)->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('pre_order_id')->nullable()->constrained('pre_orders')->cascadeOnDelete();
            $table->string('consignment_id')->nullable();
            $table->string('tracking_code')->nullable();
            $table->string('courier_name')->default('SteadFast');
            $table->string('status')->default('in_review');
            $table->text('note')->nullable();
            $table->decimal('delivery_fee', 12, 2)->default(0);
            $table->json('status_history')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'tracking_code']);
            $table->index('consignment_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courier_trackings');
    }
};
