<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\WeightDeliveryCharge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeightDeliveryChargeAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_delivery_charge_uses_area_specific_rule_before_fallback_to_global_rule(): void
    {
        $area = Area::factory()->create(['name' => 'Dhaka', 'delivery_amount' => 50]);

        WeightDeliveryCharge::create([
            'area_id' => null,
            'min_weight' => 0,
            'max_weight' => 5,
            'delivery_charge' => 120,
        ]);

        WeightDeliveryCharge::create([
            'area_id' => $area->id,
            'min_weight' => 0,
            'max_weight' => 5,
            'delivery_charge' => 200,
        ]);

        $this->assertSame(200.0, getWeightDeliveryCharge(4, $area->id));
        $this->assertSame(120.0, getWeightDeliveryCharge(4));
    }
}
