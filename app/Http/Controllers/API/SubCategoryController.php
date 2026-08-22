<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\SubCategoryResource;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->category_slug) {
            $categoryId = Category::where('slug', $request->category_slug)->value('id');
        } else {
            $request->validate([
                'category_id' => 'required|exists:categories,id',
            ]);
            $categoryId = $request->category_id;
        }

        $subCategories = SubCategory::whereHas('categories', function ($query) use ($categoryId) {
            return $query->where('category_id', $categoryId);
        })->isActive()->get();

        return $this->json('Sub categories list', [
            'sub_categories' => SubCategoryResource::collection($subCategories),
        ]);
    }
}
