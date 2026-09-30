<?php

namespace App\Repositories;

use App\Models\WeightDeliveryCharge;

class WeightDeliveryChargeRepository extends Repository
{
    public static function model()
    {
        return WeightDeliveryCharge::class;
    }

    public static function storeByRequest($request): WeightDeliveryCharge
    {
        return self::create([
            'area_id' => $request->area_id ?: null,
            'delivery_charge' => $request->delivery_charge,
            'min_weight' => $request->min_weight,
            'max_weight' => $request->max_weight,
        ]);
    }

    public static function storeBulkByRequest($request): array
    {
        $areaId = $request->area_id ?: null;
        $rules = $request->input('rules', []);
        $saved = [];

        foreach ($rules as $rule) {
            if (! isset($rule['min_weight'], $rule['max_weight'], $rule['delivery_charge'])) {
                continue;
            }

            $saved[] = self::create([
                'area_id' => $areaId ?: ($rule['area_id'] ?? null),
                'delivery_charge' => (float) $rule['delivery_charge'],
                'min_weight' => (float) $rule['min_weight'],
                'max_weight' => (float) $rule['max_weight'],
            ]);
        }

        return $saved;
    }

    public static function updateByRequest($request, WeightDeliveryCharge $deliveryCharge): WeightDeliveryCharge
    {
        $deliveryCharge->update([
            'area_id' => $request->area_id ?: null,
            'delivery_charge' => $request->delivery_charge,
            'min_weight' => $request->min_weight,
            'max_weight' => $request->max_weight,
        ]);

        return $deliveryCharge;
    }
}
