<?php

namespace App\Repositories;

use App\Http\Requests\BrandRequest;
use App\Models\Brand;
use App\Models\TranslateUtility;

class BrandRepository extends Repository
{
    /**
     * Get the model
     * model() brand
     */
    public static function model()
    {
        return Brand::class;
    }

    /**
     * store a new brand
     */
    public static function storeByRequest(BrandRequest $request): Brand
    {
        $shop = generaleSetting('rootShop');

        $brand = self::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'brand_thumbnail' => $request->thumbnail,
            'is_active' => true,
            'shop_id' => $shop->id,
        ]);

        // create translation
        foreach ($request->names ?? [] as $lang => $name) {
            if (! $lang || ! $name) {
                continue;
            }
            TranslateUtility::create([
                'brand_id' => $brand->id,
                'name' => $name,
                'lang' => $lang,
            ]);
        }

        return $brand;
    }

    /**
     * update a brand
     */
    public static function updateByRequest(BrandRequest $request, Brand $brand): Brand
    {
        $brand->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar ??  $request->name,
            'brand_thumbnail' => $request->thumbnail ?? $brand->getRawOriginal('brand_thumbnail'),
        ]);

        // update and create translation
        foreach ($request->names ?? [] as $lang => $name) {
            if (! $lang || ! $name) {
                continue;
            }
            TranslateUtility::updateOrCreate(
                [
                    'brand_id' => $brand->id,
                    'lang' => $lang,
                ],
                [
                    'name' => $name,
                ]
            );
        }

        return $brand;
    }
}
