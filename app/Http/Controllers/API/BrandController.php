<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Retrieve all active brands for the storefront.
     */
    public function index(Request $request): JsonResponse
    {
        $rootShop = generaleSetting('rootShop');
        $search = trim((string) $request->string('search'));
        $locale = $request->header('accept-language', 'en');

        $brands = Brand::query()
            ->where('shop_id', $rootShop->id)
            ->isActive()
            ->when($search !== '', function ($query) use ($search, $locale) {
                $query->where(function ($brandQuery) use ($search, $locale) {
                    $brandQuery->where('name', 'like', "%{$search}%")
                        ->orWhereHas('translations', function ($translationQuery) use ($search, $locale) {
                            $translationQuery->where('name', 'like', "%{$search}%");

                            if ($locale !== 'en') {
                                $translationQuery->where('lang', $locale);
                            }
                        });
                });
            })
            ->withCount('products')
            ->with('translations')
            ->orderBy('name')
            ->get();

        return $this->json('brands', [
            'total' => $brands->count(),
            'brands' => BrandResource::collection($brands),
        ]);
    }
}
