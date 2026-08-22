<?php

namespace App\Repositories;

use App\Http\Requests\SubCategoryRequest;
use App\Models\SubCategory;
use App\Models\TranslateUtility;
use Illuminate\Support\Str;

class SubCategoryRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return SubCategory::class;
    }

    /**
     * store a new category
     */
    public static function storeByRequest(SubCategoryRequest $request): SubCategory
    {
        $shop = generaleSetting('rootShop');

        $subCategory = self::create([
            'shop_id' => $shop->id,
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'short_description' => $request->short_description,
            'short_description_ar' => $request->short_description_ar ?? $request->short_description,
            'sub_thumbnail' => $request->thumbnail,
            'is_active' => true,
        ]);

        $subCategory->categories()->attach($request->category);

        // create translation
        foreach ($request->names ?? [] as $lang => $name) {
            TranslateUtility::create([
                'sub_category_id' => $subCategory->id,
                'name' => $name,
                'lang' => $lang,
                'slug' => Str::slug($name, '-'),
            ]);
        }

        return $subCategory;
    }

    /**
     * update a category
     */
    public static function updateByRequest(SubCategoryRequest $request, SubCategory $subCategory): SubCategory
    {

        $subCategory->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'short_description' => $request->short_description,
            'short_description_ar' => $request->short_description_ar ?? $request->short_description,
            'sub_thumbnail' => $request->thumbnail,
        ]);

        $subCategory->categories()->sync($request->category);

        // update and create translation
        foreach ($request->names ?? [] as $lang => $name) {
            TranslateUtility::updateOrCreate([
                'sub_category_id' => $subCategory->id,
                'lang' => $lang,
            ], [
                'name' => $name,
                'slug' => Str::slug($name, '-'),
            ]);
        }

        return $subCategory;
    }
}
