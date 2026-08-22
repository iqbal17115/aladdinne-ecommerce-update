<?php

use App\Models\Driver;
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
        Schema::create('driver_pre_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Driver::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PreOrder::class)->constrained()->cascadeOnDelete();
            $table->boolean('is_completed')->default(false);
            $table->string('assign_for')->nullable();
            $table->boolean('is_accept')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('driver_pre_orders');
    }
};
