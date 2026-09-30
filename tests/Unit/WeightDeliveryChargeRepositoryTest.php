<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\WeightDeliveryCharge;
use App\Repositories\WeightDeliveryChargeRepository;
use Illuminate\Http\Request;
use Tests\TestCase;

class WeightDeliveryChargeRepositoryTest extends TestCase
{
    public function test_store_bulk_by_request_creates_multiple_rows(): void
    {
        $area = Area::create([
            'name' => 'Dhaka',
            'delivery_amount' => 0,
            'distance' => 0,
            'latitude' => null,
            'longitude' => null,
            'polygon_coordinates' => null,
            'price_per_km' => 0,
            'price_per_min' => 0,
            'is_active' => true,
        ]);

        $request = new Request([
            'area_id' => $area->id,
            'rules' => [
                ['min_weight' => 0, 'max_weight' => 2, 'delivery_charge' => 50],
                ['min_weight' => 2, 'max_weight' => 5, 'delivery_charge' => 80],
            ],
        ]);

        $beforeCount = WeightDeliveryCharge::where('area_id', $area->id)->count();

        WeightDeliveryChargeRepository::storeBulkByRequest($request);

        $afterCount = WeightDeliveryCharge::where('area_id', $area->id)->count();

        $this->assertSame($beforeCount + 2, $afterCount);
        $this->assertDatabaseHas('weight_delivery_charges', [
            'area_id' => $area->id,
            'min_weight' => 0,
            'max_weight' => 2,
        ]);
    }
}
