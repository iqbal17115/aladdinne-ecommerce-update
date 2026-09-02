<?php

namespace App\Repositories;

use App\Models\Thana;

class ThanaRepository extends Repository
{
    public static function model()
    {
        return Thana::class;
    }

    public static function storeByRequest($request)
    {
        return self::model()::create([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'shipping_charge' => $request->shipping_charge,
            'is_active' => $request->has('is_active') ? true : false,
        ]);
    }

    public static function updateByRequest($request, Thana $thana)
    {
        return $thana->update([
            'area_id' => $request->area_id,
            'name' => $request->name,
            'shipping_charge' => $request->shipping_charge,
            'is_active' => $request->has('is_active') ? true : ($thana->is_active ?? false),
        ]);
    }

    public static function destroyByRequest(Thana $thana)
    {
        return self::model()::destroy($thana->id);
    }
}
