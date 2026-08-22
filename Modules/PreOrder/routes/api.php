<?php

use Illuminate\Support\Facades\Route;
use Modules\PreOrder\App\Http\Controllers\Api\PreOrderController;
use Modules\PreOrder\App\Http\Controllers\Api\SellerPreOrderController;
use Modules\PreOrder\App\Http\Controllers\Api\SellerPreOrderProductController;

Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    // order route
    Route::controller(PreOrderController::class)->prefix('pre-orders')->group(function () {
        Route::get('/', 'index');
        Route::get('/details/{preOrder}', 'show');
        Route::post('/store', 'store');
        Route::post('/checkout', 'checkout');
        Route::post('/pay', 'pay');
        Route::post('/cancel', 'cancel');
        Route::post('/refund-request', 'refundRequest');
    });
});

Route::middleware(['auth:sanctum', 'role:shop'])->prefix('/seller')->group(function () {
    // Pre-order products (add / update / status / delete / details) from the seller app.
    Route::controller(SellerPreOrderProductController::class)->prefix('pre-orders/products')->group(function () {
        Route::get('/', 'index');
        Route::post('/store', 'store');
        Route::get('/{id}/show', 'show');
        Route::post('/{id}/update', 'update');
        Route::post('/{id}/status-toggle', 'statusToggle');
        Route::delete('/{id}/destroy', 'destroy');
    });

    // Pre-orders: order list, details, status change, rider assign, commission & profit reports.
    Route::controller(SellerPreOrderController::class)->prefix('pre-orders')->group(function () {
        Route::get('/', 'index');
        Route::get('/details', 'show');
        Route::post('/status-update', 'update');
        Route::get('/riders', 'riders');
        Route::post('/assign-rider', 'assignRider');
        // Supplier list now comes from the Purchase module: GET /seller/purchases/suppliers
        Route::post('/purchase', 'storePurchase');
        Route::get('/commission-list', 'commissionList');
        Route::get('/profit-report', 'profitReport');
    });
});
