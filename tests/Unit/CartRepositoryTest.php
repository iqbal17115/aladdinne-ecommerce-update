<?php

namespace Tests\Unit;

use App\Models\Shop;
use App\Repositories\OrderRepository;
use Illuminate\Http\Request;
use ReflectionMethod;
use Tests\TestCase;

class CartRepositoryTest extends TestCase
{
    public function test_get_cart_wise_amounts_handles_missing_size_and_color_without_crashing(): void
    {
        $product = new class {
            public $id = 1;
            public $discount_price = 0;
            public $price = 100;
            public $is_digital = false;
            public $flashSales = null;
            public $quantity = 1;

            public function sizes()
            {
                return new class {
                    public function where($column, $value)
                    {
                        return $this;
                    }

                    public function first()
                    {
                        return null;
                    }
                };
            }

            public function colors()
            {
                return new class {
                    public function where($column, $value)
                    {
                        return $this;
                    }

                    public function first()
                    {
                        return null;
                    }
                };
            }
        };

        $cart = new class($product) {
            public $product;
            public $quantity = 1;
            public $shop_id = 1;
            public $size = 999;
            public $color = 999;

            public function __construct($product)
            {
                $this->product = $product;
            }
        };

        $shop = new Shop();
        $shop->id = 1;
        $shop->latitude = null;
        $shop->longitude = null;

        $request = new Request([
            'address_id' => null,
            'area_id' => null,
            'coupon_code' => null,
            'latitude' => null,
            'longitude' => null,
        ]);

        $this->app['request'] = $request;

        $method = new ReflectionMethod(OrderRepository::class, 'getCartWiseAmounts');
        $method->setAccessible(true);
        $response = $method->invoke(null, $shop, collect([$cart]), null);

        $this->assertIsArray($response);
        $this->assertSame(0.0, (float) $response['deliveryCharge']);
        $this->assertSame(100.0, (float) $response['totalAmount']);
    }
}
