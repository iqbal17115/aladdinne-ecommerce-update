<?php

namespace Modules\PreOrder\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Scopes\PreOderProduct;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Modules\PreOrder\App\Http\Requests\ProductRequest;
use Modules\PreOrder\App\Models\PreOrderSetting;
use Modules\PreOrder\App\Repositories\PreOrderProductRepository;
use Modules\PreOrder\App\Resources\SellerPreOrderProductDetailsResource;
use Modules\PreOrder\App\Resources\SellerPreOrderProductResource;

class SellerPreOrderProductController extends Controller
{
    /**
     * List the shop's pre-order products. The root shop sees every pre-order
     * product; a sub-shop sees only its own.
     */
    public function index(Request $request)
    {
        $this->authorizePreOrder();

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        $isRoot = $shop->id === $rootShop->id;

        $page = (int) ($request->page ?? 1);
        $perPage = (int) ($request->per_page ?? 20);
        $skip = ($page * $perPage) - $perPage;

        $search = $request->search;

        $query = PreOrderProductRepository::query()
            ->withoutGlobalScope(PreOderProduct::class)
            ->where('is_preorder', true)
            ->when(! $isRoot, fn ($q) => $q->where('shop_id', $shop?->id))
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%"))
            ->latest('id');

        $total = $query->count();
        $products = $query->skip($skip)->take($perPage)->get();

        return $this->json('Pre-order product list', [
            'total' => $total,
            'products' => SellerPreOrderProductResource::collection($products),
        ]);
    }

    /**
     * Store a newly created pre-order product for the shop.
     */
    public function store(ProductRequest $request)
    {
        $this->authorizePreOrder();

        $shop = generaleSetting('shop');
        $exists = PreOrderProductRepository::query()
            ->withoutGlobalScopes()
            ->where('shop_id', $shop?->id)
            ->where('code', $request->code)
            ->exists();

        if ($exists) {
            return $this->json('Product code already exists', [
                'errors' => (object) [
                    'code' => [__('The product code already exists')],
                ],
            ], 422);
        }

        $product = PreOrderProductRepository::storeByRequest($request);

        return $this->json(__('Product created successfully'), [
            'product' => SellerPreOrderProductDetailsResource::make($product),
        ]);
    }

    /**
     * Show a single pre-order product's details.
     */
    public function show($id)
    {
        $this->authorizePreOrder();

        $product = $this->findOwnedProduct($id);

        return $this->json('Pre-order product details', [
            'product' => SellerPreOrderProductDetailsResource::make($product),
        ]);
    }

    /**
     * Update the given pre-order product.
     */
    public function update(ProductRequest $request, $id)
    {
        $this->authorizePreOrder();

        $product = $this->findOwnedProduct($id);

        $shop = generaleSetting('shop');
        $exists = PreOrderProductRepository::query()
            ->withoutGlobalScopes()
            ->where('shop_id', $shop?->id)
            ->where('code', $request->code)
            ->where('id', '!=', $product->id)
            ->exists();

        if ($exists) {
            return $this->json('Product code already exists', [
                'errors' => (object) [
                    'code' => [__('The product code already exists')],
                ],
            ], 422);
        }

        $product = PreOrderProductRepository::updateByRequest($request, $product);

        return $this->json(__('Product updated successfully'), [
            'product' => SellerPreOrderProductDetailsResource::make($product),
        ]);
    }

    /**
     * Toggle the active status of the product.
     */
    public function statusToggle($id)
    {
        $this->authorizePreOrder();

        $product = $this->findOwnedProduct($id);
        $product->update(['is_active' => ! $product->is_active]);
        $product->refresh();

        return $this->json(__('Status updated successfully'), [
            'product' => SellerPreOrderProductResource::make($product),
        ]);
    }

    /**
     * Delete the product.
     */
    public function destroy($id)
    {
        $this->authorizePreOrder();

        $product = $this->findOwnedProduct($id);
        $product->delete();

        return $this->json(__('Product deleted successfully'), []);
    }

    /**
     * Resolve a pre-order product the current shop is allowed to manage. The
     * root shop can reach any pre-order product; a sub-shop only its own.
     */
    private function findOwnedProduct($id)
    {
        $product = PreOrderProductRepository::query()
            ->withoutGlobalScopes()
            ->where('is_preorder', true)
            ->find($id);

        if (! $product) {
            throw new HttpResponseException(
                $this->json(__('Pre-order product not found'), [], 404)
            );
        }

        $shop = generaleSetting('shop');
        $rootShop = generaleSetting('rootShop');
        if ($shop->id !== $rootShop->id && $product->shop_id !== $shop->id) {
            throw new HttpResponseException(
                $this->json(__('You are not allowed to access this product'), [], 403)
            );
        }

        return $product;
    }

    /**
     * Ensure the shop is allowed to use the pre-order feature.
     */
    private function authorizePreOrder(): void
    {
        $setting = PreOrderSetting::firstOrFail();
        $this->authorize('canUsePreOrder', $setting);
    }
}
