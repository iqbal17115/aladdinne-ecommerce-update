<?php

use App\Models\Shop;
use App\Models\Address;
use App\Models\Customer;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pre_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Shop::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Customer::class)->constrained()->cascadeOnDelete();
            $table->string('order_code')->index();
            $table->float('payable_amount');
            $table->float('total_amount');
            $table->float('paid_amount')->default(0);
            $table->float('delivery_charge')->default(0);
            $table->string('payment_status');
            $table->string('order_status');
            $table->boolean('is_refundable')->default(false);
            $table->unsignedBigInteger('purchase_id')->nullable();
            $table->string('refund_status')->nullable();
            $table->longText('refund_reason')->nullable();
            $table->longText('refund_note')->nullable();
            $table->float('refund_amount')->nullable()->default(0);
            $table->timestamp('refunded_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->foreignIdFor(Address::class)->nullable()->constrained()->nullOnDelete();
            $table->longText('customer_note')->nullable();
            $table->longText('admin_note')->nullable();
            $table->timestamp('pick_date')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->float('admin_commission')->nullable()->default(0);
            $table->string('currency_symbol')->nullable()->default('$');
            $table->float('currency_rate')->nullable()->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pre_orders');
    }
};
