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
        Schema::create('meta_pixel_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_name');
            $table->string('event_id')->nullable();
            $table->string('page_url', 2048)->nullable();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('guest_name')->nullable();
            $table->string('guest_email')->nullable();
            $table->json('product_ids')->nullable();
            $table->string('product_name')->nullable();
            $table->foreignId('shop_id')->nullable()->constrained()->nullOnDelete();
            $table->string('shop_name')->nullable();
            $table->decimal('value', 12, 2)->nullable();
            $table->string('currency', 8)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->index(['event_name', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meta_pixel_events');
    }
};
