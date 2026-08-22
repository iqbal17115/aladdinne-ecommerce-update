<?php

namespace App\Repositories;

use App\Models\Area;

class AreaRepository extends Repository
{
    public static function model()
    {
        return Area::class;
    }

    public static function storeByRequest($request)
    {
        return self::model()::create([
            'name' => $request->name,
            'delivery_amount' => $request->delivery_amount,
            'distance' => $request->distance ?? 0,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'polygon_coordinates' => $request->polygon_coordinates,
            'price_per_km' => $request->price_per_km,
            'price_per_min' => $request->price_per_min,
            'is_active' => $request->has('is_active') ? true : false,
        ]);
    }

    public static function updateByRequest($request, Area $area)
    {
        return $area->update([
            'name' => $request->name,
            'delivery_amount' => $request->delivery_amount,
            'distance' => $request->distance ?? $area->distance,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'polygon_coordinates' => $request->polygon_coordinates,
            'price_per_km' => $request->price_per_km,
            'price_per_min' => $request->price_per_min,
            'is_active' => $request->has('is_active') ? true : ($area->is_active ?? false),
        ]);
    }

    public static function destroyByRequest(Area $area)
    {
        return self::model()::destroy($area->id);
    }
}
