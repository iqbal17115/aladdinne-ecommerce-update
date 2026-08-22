<?php

namespace Modules\PreOrder\App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Scopes\PreOderProduct;
use Modules\PreOrder\App\Http\Requests\ProductRequest;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Repositories\PreOrderProductRepository;

class PreOrderProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $isRoot = $shop->id === $rootShop->id ? true : false;
        $preOrderSetting = PreOrderSetting::firstOrFail();
        $this->authorize('preOrderForShop', $preOrderSetting);
        $products = PreOrderProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->where('is_preorder', true)->when(!$isRoot, function ($q) use ($shop) {
            return $q->where('shop_id', $shop?->id);
        })->when(request('search'), function ($q) {
            return $q->where('name', 'like', '%' . request('search') . '%');
        })->latest('id')->paginate(20)->withQueryString();

        return view('preorder::product.index', compact('products', 'preOrderSetting', 'isRoot'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('canUsePreOrder', $setting);
        $data['project'] = config('app.project_key');
        $data['brands'] = Brand::query()->isActive()->get();
        $data['categories'] = Category::query()->active()->orderBy('name', 'asc')->get();
        $data['htmlTree'] = '';
        if ($data['project'] === 'ReadyGrocery') {
            $data['categories'] = Category::query()->whereNull('parent_id')->active()->orderBy('name', 'asc')->get();
            $data['htmlTree'] = $this->formatCategoryTree($data['categories']);
        }
        if ($data['project'] === 'ReadyEcommerce') {
            $data['units'] = \App\Models\Unit::query()->isActive()->get();
        }
        return view('preorder::product.create', $data);
    }

    private function formatCategoryTree($categories, $selectedCategories = [])
    {
        $selectedCategories = old('categories', $selectedCategories);
        $html = '<ul class="category-tree">';
        foreach ($categories as $category) {
            $checked = in_array($category->id, $selectedCategories) ? 'checked' : '';
            $html .= '<li id="' . $category->id . '" class="category">';
            $html .= '<input type="checkbox" name="categories[]" value="' . $category->id . '" id="category_' . $category->id . '" class="form-check-input category-checkbox" ' . $checked . '>';
            $html .= '<label for="category_' . $category->id . '">' . $category->name . '</label>';
            $html .= $this->formatCategoryTree($category->subCategories, $selectedCategories);
            $html .= '</li>';
        }
        $html .= '</ul>';
        return $html;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProductRequest $request)
    {
        PreOrderProductRepository::storeByRequest($request);
        return to_route('shop.preOrder.product.index')->withSuccess(__('Product created successfully'));
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $product = PreOrderProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->findOrFail($id);
        return view('shop.product.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('canUsePreOrder', $setting);
        $data['product'] = PreOrderProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->findOrFail($id);
        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        if ($shop->id !== $rootShop->id && $data['product']->shop_id !== $shop->id) {
            abort(404);
        }
        $data['project'] = config('app.project_key');
        $data['brands'] = Brand::query()->isActive()->get();
        $data['categories'] = Category::query()->active()->orderBy('name', 'asc')->get();
        $data['htmlTree'] = '';
        if ($data['project'] === 'ReadyGrocery') {
            $data['categories'] = Category::query()->whereNull('parent_id')->active()->orderBy('name', 'asc')->get();
            $data['htmlTree'] = $this->formatCategoryTree($data['categories'], $data['product']->categories?->pluck('id')->toArray());
        }
        if ($data['project'] === 'ReadyEcommerce') {
            $categoryId = $data['product']->categories()?->latest('id')->first()?->id;
            $data['subCategories'] = \App\Models\SubCategory::whereHas('categories', function ($query) use ($categoryId) {
                return $query->where('category_id', $categoryId);
            })->isActive()->get();
            $data['units'] = \App\Models\Unit::query()->isActive()->get();
        }
        $data['metaKeywords'] = explode(',', $data['product']->meta_keywords) ?: [];
        return view('preorder::product.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProductRequest $request, $id)
    {
        $product = PreOrderProductRepository::query()->withoutGlobalScope(PreOderProduct::class)->findOrFail($id);
        PreOrderProductRepository::updateByRequest($request, $product);
        return to_route('shop.preOrder.product.index')->withSuccess(__('Product updated successfully'));
    }


    /**
     * Toggle the status of the specified product.
     */
    public function statusToggle($id)
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('canUsePreOrder', $setting);
        $product = PreOrderProductRepository::query()->withoutGlobalScopes()->findOrFail($id);
        $product->update([
            'is_active' => ! $product->is_active,
        ]);
        return back()->withSuccess(__('Status updated successfully'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('canUsePreOrder', $setting);
        $product = PreOrderProductRepository::query()->withoutGlobalScopes()->findOrFail($id);
        $product->delete();
        return to_route('shop.preOrder.product.index')->withSuccess(__('Product deleted successfully'));
    }
}
