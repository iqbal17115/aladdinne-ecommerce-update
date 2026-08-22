<?php

use Illuminate\Support\Facades\Route;
use Modules\PreOrder\App\Http\Controllers\AnalyticsController;
use Modules\PreOrder\App\Http\Controllers\EarningReportController;
use Modules\PreOrder\App\Http\Controllers\PreOrderController;
use Modules\PreOrder\App\Http\Controllers\PreOrderProductController;
use Modules\PreOrder\App\Http\Controllers\PreOrderSettingController;




Route::controller(EarningReportController::class)->name('shop.')->prefix('shop')->group(function () {
    Route::get('/commission-list-download', 'commissionListExport')->name('preOrder.commissionListExport');
    Route::get('/profit-report-download', 'profitReportExport')->name('preOrder.profitReportExport');
});
Route::controller(AnalyticsController::class)->name('shop.')->prefix('shop')->group(function () {
    Route::get('/pre-order/sale-analytics', 'saleAnalytics')->name('preOrder.saleAnalytics');
});
Route::controller(PreOrderController::class)->name('shop.')->prefix('pre-order')->group(function () {
    Route::get('/invoice/{preOrder}', 'orderInvoice')->name('preOrder.invoice');
    Route::get('/payment-slip/{preOrder}', 'orderPaymentInvoice')->name('preOrder.paymentInvoice');
});
Route::middleware(['auth', 'checkPermission'])->name('shop.')->prefix('shop')->group(function () {
    Route::controller(AnalyticsController::class)->prefix('pre-order')->group(function () {
        Route::get('/analytics', 'index')->name('preOrder.analytics');
    });
    Route::controller(PreOrderController::class)->prefix('pre-orders')->group(function () {
        Route::get('/', 'index')->name('preOrder.index');
        Route::get('/{preOrder}', 'show')->name('preOrder.show');
        Route::get('/{preOrder}/status-change', 'statusChange')->name('preOrder.status.change');
        Route::get('/{preOrder}/payment-status', 'paymentStatusToggle')->name('preOrder.payment.status');
        Route::get('/{preOrder}/refund/approve', 'refundApprove')->name('preOrder.refund.approve');
        Route::post('/{preOrder}/refund/reject', 'refundReject')->name('preOrder.refund.reject');
        Route::get('/{preOrder}/refund/pay', 'refundPay')->name('preOrder.refund.pay');
        Route::post('/drivers/{preOrder}/assign-order', 'assignOrder')->name('preOrder.assign.order');
        Route::post('/{preOrder}/assign-courier', 'assignCourier')->name('preOrder.assign.courier');
        Route::post('/{preOrder}/purchase', 'storePurchase')->name('preOrder.purchase.store');
    });
    Route::controller(PreOrderProductController::class)->prefix('pre-orders/products')->group(function () {
        Route::get('/list', 'index')->name('preOrder.product.index');
        Route::get('/create', 'create')->name('preOrder.product.create');
        Route::post('/store', 'store')->name('preOrder.product.store');
        Route::get('/edit/{id}', 'edit')->name('preOrder.product.edit');
        Route::put('/update/{id}', 'update')->name('preOrder.product.update');
        Route::get('/show/{id}', 'show')->name('preOrder.product.show');
        Route::get('/toggle/{id}', 'statusToggle')->name('preOrder.product.toggle');
        Route::get('/delete/{id}', 'destroy')->name('preOrder.product.destroy');
    });
    Route::controller(PreOrderSettingController::class)->prefix('pre-order')->group(function () {
        Route::get('/setting', 'index')->name('preOrder.setting');
        Route::post('/setting-update', 'updateCreate')->name('preOrder.setting.update');
    });
    Route::controller(EarningReportController::class)->prefix('pre-order')->group(function () {
        Route::get('/commission-list', 'commissionList')->name('preOrder.CommissionList');
        Route::get('/profit-report', 'profitReport')->name('preOrder.profitReport');
    });
});


