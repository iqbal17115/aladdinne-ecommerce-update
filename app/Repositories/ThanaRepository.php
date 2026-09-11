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
            'name' => $request->name,
            'area_id' => $request->area_id,
            'is_active' => $request->has('is_active') ? true : false,
        ]);
    }

    public static function updateByRequest($request, Thana $thana)
    {
        return $thana->update([
            'name' => $request->name,
            'area_id' => $request->area_id,
            'is_active' => $request->has('is_active') ? true : ($thana->is_active ?? false),
        ]);
    }

    public static function destroyByRequest(Thana $thana)
    {
        return self::model()::destroy($thana->id);
    }
}
