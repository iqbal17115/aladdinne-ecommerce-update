<?php

namespace App\Repositories;

use App\Models\Address;
use App\Models\GeneraleSetting;
use App\Models\Product;
use App\Traits\DeliveryPriceTrait;

class PreOrderDeliveryRepository
{
    use DeliveryPriceTrait;

    /**
     * Calculate the pre-order delivery charge exactly like the normal
     * customer checkout (CartRepository::checkoutByRequest) — area based,
     * with optional distance and weight charges — but for a single product.
     */
    public static function calculate(Product $product, $request): float
    {
        $quantity = (int) ($request->quantity ?? 1);

        $address = Address::find($request->address_id);

        $deliveryLatitude = $address->latitude ?? $request->latitude ?? null;
        $deliveryLongitude = $address->longitude ?? $request->longitude ?? null;

        $resolvedAreaId = self::resolveDeliveryAreaId(
            $deliveryLatitude !== null ? (float) $deliveryLatitude : null,
            $deliveryLongitude !== null ? (float) $deliveryLongitude : null,
            $address->area_id ?? $request->area_id ?? null,
        );

        $shop = $product->shop;

        $distanceDuration = self::orderDistanceDuration(
            (float) ($shop->latitude ?? 0),
            (float) ($shop->longitude ?? 0),
            $deliveryLatitude !== null ? (float) $deliveryLatitude : 0.0,
            $deliveryLongitude !== null ? (float) $deliveryLongitude : 0.0,
        );

        $deliveryCharge = self::calculateDeliveryPrice(
            $distanceDuration['distanceKm'],
            $distanceDuration['durationMin'],
            $resolvedAreaId
        );

        // digital products never carry a delivery charge
        if ($product->is_digital == true) {
            $deliveryCharge = 0;
        }

        // weight-based delivery charge
        $weightCharge = 0;
        $settings = GeneraleSetting::first();
        if ($settings?->is_weight_charge && $product->product_weight > 0) {
            $totalWeight = $product->product_weight * $quantity;
            if ($totalWeight > 0) {
                $weightCharge = getWeightDeliveryCharge($totalWeight);
            }
        }

        $deliveryCharge += $weightCharge;

        return (float) round($deliveryCharge, 2);
    }
}
