<?php

namespace App\Repositories;

use App\Http\Requests\CartRequest;
use App\Http\Resources\ColorResource;
use App\Http\Resources\SizeResource;
use App\Models\Address;
use App\Models\Cart;
use App\Models\GeneraleSetting;
use App\Models\Product;
use App\Models\Shop;
use App\Traits\DeliveryPriceTrait;
use Illuminate\Support\Number;
use Mpdf\Tag\B;

class CartRepository extends Repository
{
    use DeliveryPriceTrait;
    public static function model()
    {
        return Cart::class;
    }

    public static function ShopWiseCartProducts($groupCart)
    {
        $totalItems = 0;
        $shopWiseProducts = collect([]);
        $info = null;

        foreach ($groupCart as $key => $products) {
            $productArray = collect([]);

            foreach ($products as $cart) {

                $product = $cart->product;

                if (! $product) {
                    $cart->delete();
                    $info = 'Some products are removed from cart due to unavailability';
                    continue;
                }

                $totalItems++;

                $discountPercentage = $product->getDiscountPercentage($product->price, $product->discount_price);

                $totalSold = $product->orders->sum('pivot.quantity');

                $flashSale = $product->flashSales?->first();
                $flashSaleProduct = null;
                $quantity = null;

                if ($flashSale) {
                    $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                    $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

                    if ($quantity == 0) {
                        $quantity = null;
                        $flashSaleProduct = null;
                    } else {
                        $discountPercentage = $flashSale?->pivot->discount;
                    }
                }

                $size = $product->sizes()?->where('id', $cart->size)->first();
                $color = $product->colors()?->where('id', $cart->color)->first();

                $sizePrice = $size?->pivot?->price ?? 0;
                $colorPrice = $color?->pivot?->price ?? 0;
                $extraPrice = $sizePrice + $colorPrice;

                $discountPrice = $product->discount_price > 0 ? ($product->discount_price + $extraPrice) : 0;
                if ($flashSaleProduct) {
                    $discountPrice = $flashSaleProduct->pivot->price + $extraPrice;
                }

                $mainPrice = $product->price + $extraPrice;

                // calculate vat taxes
                $priceTaxAmount = 0;
                $discountTaxAmount = 0;
                foreach ($product->vatTaxes ?? [] as $tax) {
                    if ($tax->percentage > 0) {
                        $priceTaxAmount += $mainPrice * ($tax->percentage / 100);
                        $discountPrice > 0 ? $discountTaxAmount += $discountPrice * ($tax->percentage / 100) : null;
                    }
                }

                $mainPrice += $priceTaxAmount;
                $discountPrice > 0 ? $discountPrice += $discountTaxAmount : null;

                if ($discountPrice > 0) {
                    $discountPercentage = ($mainPrice - $discountPrice) / $mainPrice * 100;
                }

                $productArray[] = (object) [
                    'id' => $product->id,
                    'cart_id' => $cart->id,
                    'quantity' => (int) $cart->quantity,
                    'is_digital' => (bool) $product?->is_digital,
                    'name' => $product->name,
                    'thumbnail' => $product->thumbnail,
                    'brand' => $product->brand?->name ?? null,
                    'price' => (float) number_format($mainPrice, 2, '.', ''),
                    'discount_price' => (float) number_format($discountPrice, 2, '.', ''),
                    'discount_percentage' => (float) number_format($discountPercentage, 2, '.', ''),
                    'discount_type' => (bool) $product->discount_type,
                    'discount'    => (float) $product->discount,
                    'rating' => (float) $product->averageRating,
                    'total_reviews' => (string) Number::abbreviate($product->reviews->count(), maxPrecision: 2),
                    'total_sold' => (string) number_format($totalSold, 0, '.', ','),
                    'color' => $color ? ColorResource::make($color) : null,
                    'size' => $size ? SizeResource::make($size) : null,
                    'unit' => $cart->unit,
                ];
            }

            if ($productArray->isEmpty()) {
                continue;
            }

            $shop = $products[0]?->shop;

            $lastOnline = $shop->last_online >= now() ? true : false;

            $shopWiseProducts[] = (object) [
                'shop_id' => $key,
                'shop_name' => $shop->name,
                'shop_logo' => $shop->logo,
                'shop_rating' => (float) $shop->averageRating,
                'shop_online' => $lastOnline,
                'products' => $productArray,
            ];
        }

        return [
            'total_items' => $totalItems,
            'shop_wise_products' => $shopWiseProducts,
            'info' => $info,
        ];
    }

    /**
     * Store or update cart by request.
     */
    public static function storeOrUpdateByRequest(CartRequest $request, Product $product): Cart
    {
        $size = $request->size;
        $color = $request->color;
        $unit = $request->unit ?? $product->unit?->name;

        $tokens = cartAccessToken(request());

        $isBuyNow = $request->is_buy_now ?? false;

        // $customer = auth()->user()->customer;

        // Match on the variant too, so different color/size selections of the
        // same product become separate cart rows instead of overwriting each other.
        $cart = userCart($request)
            ->where('product_id', $product->id)
            ->where('is_buy_now', $isBuyNow)
            ->where('color', $color)
            ->where('size', $size)
            ->where('unit', $unit)
            ->first();

        if ($cart) {
            $cart->update([
                'quantity' => $isBuyNow ? 1 : $cart->quantity + 1,
            ]);

            return $cart;
        }

        return self::create([
            'product_id' => $request->product_id,
            'shop_id' => $product->shop->id,
            'is_buy_now' => $isBuyNow,
            // 'customer_id' => $customer->id,
            'customer_id' => $tokens['customer_id'] ?? null,
            'quantity' => $request->quantity ?? 1,
            'size' => $size,
            'color' => $color,
            'unit' => $unit,
            'access_token' => $tokens['access_token'] ?? ''
        ]);
    }

    private static function getDeliveryAmount()
    {
        if ($address = Address::find(request()->address_id)) {
            return $address->deliveryAmount() ?? 0;
        }

        // Check for area if no address
        return Area::find(request()->area_id)?->delivery_amount ?? 0;
    }

    public static function checkoutByRequest($request, $carts)
    {
        $totalAmount = 0;
        $deliveryCharge = 0;
        $weightCharge = 0;
        $couponDiscount = 0;
        $payableAmount = 0;

        $shopWiseTotalAmount = [];
        $totalOrderTaxAmount = 0;
        $vatTaxesArray = [];
        $tokens = cartAccessToken(request());

        $shop = Shop::find($request->shop_ids[0]);
        $address = Address::find($request->address_id);

        $deliveryLatitude = $address->latitude ?? $request->latitude;
        $deliveryLongitude = $address->longitude ?? $request->longitude;

        $resolvedAreaId = self::resolveDeliveryAreaId(
            $deliveryLatitude !== null ? (float) $deliveryLatitude : null,
            $deliveryLongitude !== null ? (float) $deliveryLongitude : null,
            $address->area_id ?? $request->area_id,
        );

        // Distance/duration needs both ends of the trip — fall back to a flat
        // (area-rate-only) delivery charge when either side is missing.
        $distanceDuration = ($shop?->latitude !== null && $shop?->longitude !== null && $deliveryLatitude !== null && $deliveryLongitude !== null)
            ? static::orderDistanceDuration(
                (float) $shop->latitude,
                (float) $shop->longitude,
                (float) $deliveryLatitude,
                (float) $deliveryLongitude,
            )
            : ['distanceKm' => 0.0, 'durationMin' => 0.0];


        foreach ($carts ?? [] as $cart) {

            if (! $cart) {
                continue;
            }

            $product = $cart->product;
            $flashSale = $product->flashSales?->first();
            $flashSaleProduct = null;
            $quantity = null;

            $price = $product->discount_price > 0 ? $product->discount_price : $product->price;

            if ($flashSale) {
                $flashSaleProduct = $flashSale?->products()->where('id', $product->id)->first();

                $quantity = $flashSaleProduct?->pivot->quantity - $flashSaleProduct->pivot->sale_quantity;

                if ($quantity == 0) {
                    $quantity = null;
                    $flashSaleProduct = null;
                } else {
                    $price = $flashSaleProduct->pivot->price;
                }
            }

            $sizePrice = $product->sizes()?->where('id', $cart->size)->first()?->pivot?->price ?? 0;
            $price = $price + $sizePrice;

            $colorPrice = $product->colors()?->where('id', $cart->color)->first()?->pivot?->price ?? 0;
            $price = $price + $colorPrice;

            // get shop wise total amount
            $shop = $product->shop;
            if (array_key_exists($shop->id, $shopWiseTotalAmount)) {
                $currentAmount = $shopWiseTotalAmount[$shop->id];
                $shopWiseTotalAmount[$shop->id] = $currentAmount + ($price * $cart->quantity);
            } else {
                $shopWiseTotalAmount[$shop->id] = $price * $cart->quantity;
            }

            $totalAmount += $price * $cart->quantity;
        }

        $groupCarts = $carts->groupBy('shop_id');

        // get delivery charge
        $deliveryCharge = self::calculateDeliveryPrice(
            $distanceDuration['distanceKm'],
            $distanceDuration['durationMin'],
            $resolvedAreaId
        );
        foreach ($groupCarts as $shopId => $shopCarts) {

            $productQty = 0;

            foreach ($shopCarts as $cart) {
                $productQty += $cart->quantity;
            }

            if ($productQty > 0) {
                if ($cart->product?->is_digital == true) {
                    $deliveryCharge = 0;
                }
            }
        }

        // calculate weight-based delivery charge
        $settings = GeneraleSetting::first();
        if ($settings?->is_weight_charge) {
            $totalWeight = 0;
            foreach ($carts ?? [] as $cart) {
                $product = $cart->product;
                if ($product && $product->product_weight > 0) {
                    $totalWeight += $product->product_weight * $cart->quantity;
                }
            }
            if ($totalWeight > 0) {
                $weightCharge = getWeightDeliveryCharge($totalWeight);
            }
        }

        $deliveryCharge += $weightCharge;

        // generate array for get discount
        $products = collect([]);
        foreach ($carts as $cart) {
            $products->push([
                'id' => $cart->product_id,
                'quantity' => (int) $cart->quantity,
                'shop_id' => $cart->shop_id,
            ]);
        }
        $array = (object) [
            'coupon_code' => $request->coupon_code,
            'products' => $products,
        ];

        if ($tokens['customer_id']) {
            // get coupon discount
            $getDiscount = CouponRepository::getCouponDiscount($array);

            $couponDiscount = $getDiscount['discount_amount'];
        }

        $payableAmount = $totalAmount + $deliveryCharge - $couponDiscount;

        // get order base tax
        $vatTaxes = VatTaxRepository::getActiveVatTaxes();

        foreach ($shopWiseTotalAmount as $shopId => $subtotal) {

            $thisFinalTax = [];

            foreach ($vatTaxes as $vatTax) {
                if ($vatTax->name && $vatTax->percentage > 0) {

                    $totalTaxAmount = round($subtotal * ($vatTax->percentage / 100), 2);

                    if (array_key_exists($vatTax->id, $thisFinalTax)) {
                        $currentAmount = $thisFinalTax[$vatTax->id];
                        $thisFinalTax[$vatTax->id] = $currentAmount + $totalTaxAmount;
                    } else {
                        $thisFinalTax[$vatTax->id] = $totalTaxAmount;
                    }
                    $totalOrderTaxAmount += $totalTaxAmount;
                }
            }

            $vatTaxesArray = $vatTaxes->map(function ($vatTax) use ($thisFinalTax) {
                return [
                    'id' => $vatTax->id,
                    'name' => $vatTax->name,
                    'percentage' => $vatTax->percentage,
                    'amount' => $thisFinalTax[$vatTax->id] ?? 0,
                ];
            })->toArray();
        }

        $payableAmount += $totalOrderTaxAmount;

        return [
            'total_amount' => (float) round($totalAmount, 2),
            'delivery_charge' => (float) round($deliveryCharge, 2),
            'coupon_discount' => (float) round($couponDiscount, 2),
            'order_tax_amount' => (float) round($totalOrderTaxAmount, 2),
            'payable_amount' => (float) round($payableAmount, 2),
            'all_vat_taxes' => $vatTaxesArray,
        ];
    }
}
