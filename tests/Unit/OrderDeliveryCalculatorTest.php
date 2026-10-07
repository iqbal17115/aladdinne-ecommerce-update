<?php

namespace Tests\Unit;

use App\Models\Area;
use App\Models\GeneraleSetting;
use App\Models\Product;
use App\Models\Shop;
use App\Models\User;
use App\Models\WeightDeliveryCharge;
use App\Repositories\OrderRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderDeliveryCalculatorTest extends TestCase
{
    use RefreshDatabase;

    public function test_delivery_charge_recalculates_from_current_order_product_quantities(): void
    {
        $area = Area::query()->create([
            'name' => 'Test Area',
            'delivery_amount' => 10,
        ]);
        $owner = User::factory()->create();
        $shop = Shop::query()->create([
            'name' => 'Test Shop',
            'user_id' => $owner->id,
            'latitude' => 23.6850,
            'longitude' => 90.5155,
        ]);
        $product = Product::query()->create([
            'name' => 'Test Product',
            'shop_id' => $shop->id,
            'price' => 100,
            'quantity' => 10,
            'product_weight' => 2,
            'is_active' => true,
        ]);
        GeneraleSetting::query()->create([
            'name' => 'default',
            'is_weight_charge' => true,
            'is_distance_charge' => false,
            'is_delivery_free' => false,
        ]);
        WeightDeliveryCharge::query()->create([
            'area_id' => $area->id,
            'min_weight' => 0,
            'max_weight' => 2,
            'delivery_charge' => 120,
        ]);
        WeightDeliveryCharge::query()->create([
            'area_id' => $area->id,
            'min_weight' => 3,
            'max_weight' => 10,
            'delivery_charge' => 240,
        ]);

        $firstCharge = OrderRepository::calculateDeliveryCharge(
            $shop,
            collect([['product' => $product, 'quantity' => 1]]),
            $area->id,
        );
        $secondCharge = OrderRepository::calculateDeliveryCharge(
            $shop,
            collect([['product' => $product, 'quantity' => 3]]),
            $area->id,
        );

        $this->assertSame(120.0, $firstCharge);
        $this->assertSame(240.0, $secondCharge);
    }
}
