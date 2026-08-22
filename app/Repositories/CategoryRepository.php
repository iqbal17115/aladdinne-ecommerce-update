<?php

namespace App\Repositories;

use App\Http\Requests\CategoryRequest;
use App\Models\Category;
use App\Models\TranslateUtility;
use Illuminate\Support\Str;

class CategoryRepository extends Repository
{
    /**
     * base method
     *
     * @method model()
     */
    public static function model()
    {
        return Category::class;
    }

    /**
     * store a new category
     */
    public static function storeByRequest(CategoryRequest $request): Category
    {
        $category = self::create([
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name,
            'description' => $request->description,
            'description_ar' => $request->description_ar ?? $request->description,
            'status' => true,
            'order_by' => $request->input('order_by', 0),
            'icon' => $request->icon,
            'category_thumbnail' => $request->thumbnail,
            'banner' => $request->banner,
        ]);

        // create translation
        foreach ($request->names ?? [] as $lang => $name) {
            if (! $lang || ! $name) {
                continue;
            }
            TranslateUtility::create([
                'category_id' => $category->id,
                'name' => $name,
                'lang' => $lang,
                'slug' => Str::slug($name, '-'),
            ]);
        }

        return $category;
    }

    /**
     * update a category
     */
    public static function updateByRequest(CategoryRequest $request, Category $category): Category
    {
        $category->update([
            'name' => $request->name,
            'name_ar' => $request->name_ar ?? $request->name_ar,
            'description' => $request->description,
            'description_ar' => $request->description_ar ?? $request->description,
            'order_by' => $request->input('order_by', $category->order_by ?? 0),
            'icon' => $request->filled('icon') ? $request->icon : $category->getRawOriginal('icon'),
            'category_thumbnail' => $request->filled('thumbnail') ? $request->thumbnail : $category->getRawOriginal('category_thumbnail'),
            'banner' => $request->filled('banner') ? $request->banner : $category->getRawOriginal('banner'),
        ]);

        // update and create translation
        foreach ($request->names ?? [] as $lang => $name) {
            if (! $lang || ! $name) {
                continue;
            }
            TranslateUtility::updateOrCreate([
                'category_id' => $category->id,
                'lang' => $lang,
            ], [
                'name' => $name,
                'slug' => Str::slug($name, '-'),
            ]);
        }

        return $category;
    }
}
