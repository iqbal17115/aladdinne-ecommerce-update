<?php

namespace Modules\Purchase\App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Scopes\PreOderProduct;
use App\Rules\EmailRule;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Modules\Purchase\App\Repositories\SupplierRepository;
use Modules\Purchase\App\Http\Requests\PurchaseAttachProductRequest;
use Modules\Purchase\App\Http\Requests\PurchaseRequest;
use Modules\Purchase\App\Models\Purchase;
use Modules\Purchase\App\Models\PurchaseProduct;
use Modules\Purchase\App\Models\Supplier;
use Modules\Purchase\App\Repositories\PurchaseRepository;
use Modules\Purchase\App\Repositories\SupplierTransactionRepository;
use Modules\Purchase\App\Resources\ProductSearchResource;
use Modules\Purchase\App\Resources\PurchaseDetailsResource;
use Modules\Purchase\App\Resources\PurchaseListResource;

/**
 * Seller app — Purchase. Mirrors the web dashboard purchase flow: list a shop's
 * purchases, search its products, create a purchase, attach products (by
 * quantity or scanned barcodes), and mark a purchase received. All queries are
 * scoped to the authenticated shop.
 */
class SellerPurchaseController extends Controller
{
    /**
     * List the shop's purchases.
     */
    public function index(Request $request)
    {
        $shop = generaleSetting('shop');

        $perPage = (int) ($request->per_page ?? 20);
        $search = $request->search;
        $supplierId = $request->supplier_id;

        $purchases = PurchaseRepository::query()
            ->with('supplier')
            ->where('shop_id', $shop?->id)
            ->when($supplierId, fn ($q) => $q->where('supplier_id', $supplierId))
            ->when($search, fn ($q) => $q->where('purchase_code', 'like', "%$search%"))
            ->latest('id')
            ->paginate($perPage);

        return $this->json('Purchase list', [
            'total' => $purchases->total(),
            'purchases' => PurchaseListResource::collection($purchases),
        ]);
    }

    /**
     * Supplier list for the "create purchase" dropdown and the supplier screen.
     * By default only active suppliers; pass `?all=1` to include inactive ones.
     */
    public function suppliers(Request $request)
    {
        $shop = generaleSetting('shop');
        $search = $request->search;

        $suppliers = Supplier::where('shop_id', $shop?->id)
            ->when(! $request->boolean('all'), fn ($q) => $q->where('is_active', true))
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%"))
            ->latest('id')
            ->get()
            ->map(fn ($supplier) => $this->supplierArray($supplier));

        return $this->json('Supplier list', [
            'suppliers' => $suppliers,
        ]);
    }

    /**
     * Add a new supplier for the current shop (creates the linked user account).
     */
    public function storeSupplier(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'numeric', 'unique:users,phone'],
            'email' => ['required', 'email', 'unique:users,email', new EmailRule],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,gif,svg,webp'],
        ]);

        $supplier = SupplierRepository::storeByRequest($request);

        return $this->json(__('Supplier created successfully'), [
            'supplier' => $this->supplierArray($supplier),
        ]);
    }

    /**
     * Edit a supplier owned by the current shop.
     */
    public function updateSupplier($id, Request $request)
    {
        $supplier = $this->findOwnedSupplier($id);
        $userId = $supplier->user_id;

        $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'phone' => ['required', 'numeric', 'unique:users,phone,' . $userId],
            'email' => ['required', 'email', 'unique:users,email,' . $userId, new EmailRule],
            'address' => ['nullable', 'string', 'max:255'],
            'profile_photo' => ['nullable', 'image', 'max:2048', 'mimes:png,jpg,jpeg,gif,svg,webp'],
        ]);

        $supplier = SupplierRepository::updateByRequest($request, $supplier);

        return $this->json(__('Supplier updated successfully'), [
            'supplier' => $this->supplierArray($supplier->fresh()),
        ]);
    }

    /**
     * Search the shop's active products to add to a purchase.
     */
    public function products(Request $request)
    {
        $shop = generaleSetting('shop');
        $search = $request->search;

        $products = Product::where('shop_id', $shop?->id)
            ->when($search, fn ($q) => $q->where('name', 'like', "%$search%"))
            ->isActive()
            ->limit(10)
            ->get();

        return $this->json('Products', [
            'products' => ProductSearchResource::collection($products),
        ]);
    }

    /**
     * Create a purchase. The supplier must belong to the authenticated shop.
     */
    public function store(PurchaseRequest $request)
    {
        $shop = generaleSetting('shop');

        $supplierOwned = Supplier::where('id', $request->supplier_id)
            ->where('shop_id', $shop?->id)
            ->exists();
        if (! $supplierOwned) {
            return $this->json(__('Supplier not found'), [], 404);
        }

        $purchase = PurchaseRepository::storeByRequest($request);

        return $this->json(__('Purchase created successfully'), [
            'purchase' => PurchaseDetailsResource::make($purchase->load('supplier', 'purchaseProducts.product')),
        ]);
    }

    /**
     * Show a single purchase with its products.
     */
    public function show($id)
    {
        $purchase = $this->findOwnedPurchase($id);

        return $this->json('Purchase details', [
            'purchase' => PurchaseDetailsResource::make($purchase->load('supplier', 'purchaseProducts.product')),
        ]);
    }

    /**
     * Attach a product to a purchase. Without barcodes it is a quantity-based
     * stock-in; with `product_barcodes[]` each barcode becomes a product SKU.
     */
    public function attachProduct($id, PurchaseAttachProductRequest $request)
    {
        $purchase = $this->findOwnedPurchase($id);
        $shop = generaleSetting('shop');

        $product = Product::withoutGlobalScope(PreOderProduct::class)
            ->where('shop_id', $shop?->id)
            ->find($request->product_id);
        if (! $product) {
            return $this->json(__('Product not found'), [], 404);
        }

        // Quantity-based purchase (no barcode scanning) needs a positive quantity.
        $hasBarcodes = $request->filled('product_barcodes');
        if (! $hasBarcodes) {
            if ((int) $request->quantity <= 0) {
                return $this->json(__('Add quantity first'), [], 422);
            }
            $request->merge(['is_sku' => 'false']);
        }

        $purchaseProduct = PurchaseProduct::where('purchase_id', $purchase->id)
            ->where('product_id', $product->id)
            ->first();

        PurchaseRepository::attachBarcode($product, $purchase, $purchaseProduct, $request);

        return $this->json(__('Product added to purchase successfully'), [
            'purchase' => PurchaseDetailsResource::make($purchase->fresh()->load('supplier', 'purchaseProducts.product')),
        ]);
    }

    /**
     * Mark a purchase received — records the supplier credit (and any paid
     * amount as a debit), same as the dashboard.
     */
    public function makeReceived($id, Request $request)
    {
        $purchase = $this->findOwnedPurchase($id);

        if ($purchase->purchaseProducts()->count() <= 0) {
            return $this->json(__('Product not found'), [], 422);
        }

        if ($purchase->is_received) {
            return $this->json(__('This purchase is already received'), [], 422);
        }

        $purchase->update(['is_received' => true]);

        $totalAmount = $purchase->total_amount;
        $paidAmount = $purchase->paid_amount;

        $transactionData = [
            'transaction_date' => now()->format('Y-m-d'),
            'note' => $purchase->note,
            'title' => null,
        ];

        if ($totalAmount > 0) {
            $transactionData['title'] = 'Purchase Invoice';
            SupplierTransactionRepository::storeByRequest($purchase->supplier, 'credit', $totalAmount, $transactionData, $request);
        }

        if ($paidAmount > 0) {
            $transactionData['title'] = 'Supplier Payment from purchase';
            SupplierTransactionRepository::storeByRequest($purchase->supplier, 'debit', $paidAmount, $transactionData, $request);
        }

        return $this->json(__('Purchase received successfully'), [
            'purchase' => PurchaseDetailsResource::make($purchase->fresh()->load('supplier', 'purchaseProducts.product')),
        ]);
    }

    /**
     * Resolve a purchase owned by the authenticated shop, or 404.
     */
    private function findOwnedPurchase($id): Purchase
    {
        $shop = generaleSetting('shop');

        $purchase = PurchaseRepository::query()
            ->where('shop_id', $shop?->id)
            ->find($id);

        if (! $purchase) {
            throw new HttpResponseException(
                $this->json(__('Purchase not found'), [], 404)
            );
        }

        return $purchase;
    }

    /**
     * Resolve a supplier owned by the authenticated shop, or 404.
     */
    private function findOwnedSupplier($id): Supplier
    {
        $shop = generaleSetting('shop');

        $supplier = Supplier::where('shop_id', $shop?->id)->find($id);

        if (! $supplier) {
            throw new HttpResponseException(
                $this->json(__('Supplier not found'), [], 404)
            );
        }

        return $supplier;
    }

    /**
     * Shape a supplier for the seller app (list / create / update responses).
     */
    private function supplierArray(Supplier $supplier): array
    {
        return [
            'id' => $supplier->id,
            'name' => $supplier->name,
            'phone' => $supplier->phone,
            'email' => $supplier->email,
            'address' => $supplier->address,
            'balance' => (float) number_format($supplier->balance ?? 0, 2, '.', ''),
            'is_active' => (bool) $supplier->is_active,
            'thumbnail' => $supplier->thumbnail,
        ];
    }
}
