<?php

use Illuminate\Support\Facades\Route;
use Modules\Report\App\Http\Controllers\ReportController;
use Modules\Report\App\Http\Controllers\AnalyticsController;
use Modules\Report\App\Http\Controllers\ReportExportController;
use Modules\Report\App\Http\Controllers\EarningExportController;
use Modules\Report\App\Http\Controllers\EarningReportController;



Route::controller(ReportExportController::class)->name('shop.')->prefix('reports')->group(function () {
    Route::get('/sale-product-download', 'productReportExport')->name('report.saleProductExport');
    Route::get('/wish-list-download', 'wishListExport')->name('report.wishListExport');
    Route::get('/add-to-cart-download', 'addToCartExport')->name('report.addToCartExport');
    Route::get('/product-seach-download', 'productSearchExport')->name('report.productSearchExport');
});
Route::controller(EarningExportController::class)->name('shop.')->group(function () {
    Route::get('/commission-list-download', 'commissionListExport')->name('report.commissionListExport');
});
Route::controller(AnalyticsController::class)->prefix('reports')->group(function () {
    Route::get('/sale-analytics', 'saleAnalytics')->name('report.saleAnalytics');
    Route::get('/cost-analytics', 'costAnalytics')->name('report.costAnalytics');
    Route::get('/earning-analytics', 'earningAnalytics')->name('report.earningAnalytics');
});
Route::middleware(['auth', 'checkPermission'])->name('shop.')->prefix('shop')->group(function () {
    Route::controller(AnalyticsController::class)->prefix('reports')->group(function () {
        Route::get('/analytics', 'index')->name('report.analytics');
    });
    Route::controller(ReportController::class)->prefix('reports')->group(function () {
        Route::get('/sale-product', 'saleProductReport')->name('report.saleProduct');
        Route::get('/wish-list', 'wishListReport')->name('report.wishList');
        Route::get('/add-to-cart', 'addToCartReport')->name('report.addToCart');
        Route::get('/product-search', 'productSearchReport')->name('report.productSearch');
    });
    Route::controller(EarningReportController::class)->group(function () {
        Route::get('/earning-summary', 'earningSummary')->name('report.earningSummary');
        Route::get('/commission-list', 'commissionList')->name('report.commissionList');
        Route::get('/pay-out', 'payOut')->name('report.payOut');
    });
});

