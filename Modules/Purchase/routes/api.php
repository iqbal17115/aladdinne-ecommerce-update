<?php

use Illuminate\Support\Facades\Route;
use Modules\Purchase\App\Http\Controllers\Api\SellerPurchaseController;

/*
|--------------------------------------------------------------------------
| Seller App API — Purchase
|--------------------------------------------------------------------------
| Mirrors the web dashboard "purchase a product" flow for the seller app:
| create a purchase, search products, attach a product (quantity or barcode),
| mark it received, and list / view purchases. Supplier list is read-only.
*/

Route::middleware(['auth:sanctum', 'role:shop'])->prefix('/seller')->group(function () {
    Route::controller(SellerPurchaseController::class)->prefix('purchases')->group(function () {
        Route::get('/', 'index');

        // Suppliers: read-only list + add / edit.
        Route::get('/suppliers', 'suppliers');
        Route::post('/suppliers/store', 'storeSupplier');
        Route::post('/suppliers/{id}/update', 'updateSupplier');

        Route::get('/products', 'products');
        Route::post('/store', 'store');
        Route::get('/{id}/show', 'show');
        Route::post('/{id}/attach-product', 'attachProduct');
        Route::post('/{id}/make-received', 'makeReceived');
    });
});
