<?php

use App\Http\Controllers\Admin\CheckOnlineUserController;
use App\Http\Controllers\API\Auth\AuthController;
use App\Http\Controllers\API\ProductLicenseController;
use App\Http\Controllers\CreateSuperAdmin;
use App\Http\Controllers\Gateway\Bkash\ExecutePaymentController;
use App\Http\Controllers\Gateway\JazzCash\ProcessController as JazzCashProcessController;
use App\Http\Controllers\Gateway\Nagad\CallbackController as NagadCallbackController;
use App\Http\Controllers\Gateway\PaymentGatewayController;
use App\Http\Controllers\Gateway\Rocket\ProcessController as RocketProcessController;
use App\Http\Controllers\Gateway\Sslcommerz\CallbackController as SslcommerzCallbackController;
use App\Http\Controllers\Gateway\PayPal\ProcessController as PayPalProcessController;
use App\Http\Controllers\Gateway\PayTabs\ProcessController;
use App\Http\Controllers\Gateway\PayU\ProcessController as PayUProcessController;
use App\Http\Controllers\PassportStorageSupportController;
use App\Http\Controllers\Shop\SubscriptionController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Web installer — public, and skipped by the EnsureInstalled gate
Route::prefix('install')->name('install.')->controller(\App\Http\Controllers\Install\InstallController::class)->group(function () {
    Route::get('/', 'requirements')->name('requirements');
    Route::post('/verify-token', 'verifyToken')->name('verify-token');
    Route::get('/database', 'database')->name('database');
    Route::post('/database', 'saveDatabase')->name('database.save');
    Route::get('/migrate', 'migrate')->name('migrate');
    Route::post('/migrate', 'runMigrate')->name('migrate.run');
    Route::get('/admin', 'admin')->name('admin');
    Route::post('/admin', 'saveAdmin')->name('admin.save');
    Route::get('/finished', 'finished')->name('finished');
});

Route::controller(CreateSuperAdmin::class)->middleware('guest')->group(function(){
    Route::get('/create-root', 'index')->name('create.root');
    Route::any('/create-superadmin', 'store')->name('create.superadmin');
});
Route::get('api/download-product-license/{license}', [ProductLicenseController::class, 'downloadLicensePdf'])->name('download.product.license.web');

// Change language
Route::get('/change-language', function () {
    if (request()->language) {
        App::setLocale(request()->language);
        session()->put('locale', request()->language);
    }

    return back();
})->name('change.language');

// Install Passport and storage routes.
// These run destructive maintenance commands (migrate:fresh, storage:link,
// passport:install) so they MUST be POST (CSRF-protected) and restricted to an
// authenticated root admin — otherwise an anonymous GET to /seeder-run wipes the
// entire database.
Route::controller(PassportStorageSupportController::class)
    ->middleware(['auth', 'role:root', 'demoMode'])
    ->group(function () {
        Route::post('/install-passport', 'index')->name('passport.install.index');
        Route::post('/seeder-run', 'seederRun')->name('seeder.run.index');
        Route::post('/storage-install', 'storageInstall')->name('storage.install.index');
    });

Route::controller(AuthController::class)->group(function () {
    Route::get('/auth/{provider}/callback', 'callback');
    Route::post('/auth/{provider}/callback', 'callback');
});

// Payment gateway routes
Route::get('/pay-via/pau-u/{payment}/', [PayUProcessController::class, 'index'])->name('pay-via.payu');
Route::get('/pay-via/jazzcash/{payment}/', [JazzCashProcessController::class, 'index'])->name('pay-via.jazzcash');
Route::get('/pay-via/rocket/{payment}/', [RocketProcessController::class, 'index'])->name('pay-via.rocket');
Route::match(['get', 'post'], '/nagad/{payment}/callback', [NagadCallbackController::class, 'index'])->name('nagad.payment.callback');

// SSLCommerz callbacks
Route::match(['get', 'post'], '/sslcommerz/{payment}/success', [SslcommerzCallbackController::class, 'success'])->name('sslcommerz.payment.success');
Route::match(['get', 'post'], '/sslcommerz/{payment}/fail', [SslcommerzCallbackController::class, 'fail'])->name('sslcommerz.payment.fail');
Route::match(['get', 'post'], '/sslcommerz/{payment}/cancel', [SslcommerzCallbackController::class, 'cancel'])->name('sslcommerz.payment.cancel');
Route::post('/sslcommerz/{payment}/ipn', [SslcommerzCallbackController::class, 'ipn'])->name('sslcommerz.payment.ipn');
Route::controller(PaymentGatewayController::class)->group(function () {
    // payment routes
    Route::get('/order/{payment}/payment', 'payment')->name('order.payment');

    // success and cancel routes for payment
    Route::any('/order/{payment}/payment/success', 'paymentSuccess')->name('order.payment.success');
    Route::get('/order/{payment}/payment/cancel', 'paymentCancel')->name('order.payment.cancel');

    // success and cancel routes for callback
    Route::any('/payment/{payment}/callback-success', 'success')->name('payment.success');
    Route::post('/payment/callback-success', 'success')->name('payment.success.post');
    Route::any('/payment/{payment}/callback-cancel', 'cancel')->name('payment.cancel');
});

//Paypal Payment success
Route::get('/paypal/{paypalPayment}/success', [PayPalProcessController::class, 'success'])->name('paypal.payment.success');

// Bkash Payment execute
Route::get('/bkash-payment/{payment}/execute', [ExecutePaymentController::class, 'index'])->name('bkash.payment.execute');

// Paytabs payment execute
Route::match(['get', 'post'], '/paytabs/{payment}/callback', [ProcessController::class, 'callback'])->name('paytabs.payment.callback');
Route::match(['get', 'post'], '/subscription/payment/{payment}/success', [SubscriptionController::class, 'paymentSuccess'])->name('subscription.payment.success');
Route::match(['get', 'post'], '/subscription/payment/{payment}/cancel', [SubscriptionController::class, 'paymentCancel'])->name('subscription.payment.cancel');


// check user is online or not
Route::post('/update/last/seen', [CheckOnlineUserController::class, 'checkOnlineStatus']);


// firebase messaging service worker
Route::get('/firebase-messaging-sw.js', function () {
    $fcmConfig = \App\Models\GeneraleSetting::first()?->fcm_web_config;
    $fcmConfig = $fcmConfig ? json_decode($fcmConfig, true) : [];

    if (empty($fcmConfig) || empty($fcmConfig['apiKey'])) {
        $fcmConfig = [];
    }

    $configJson = json_encode($fcmConfig);

    $content = <<<JS
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

let firebaseConfig = {$configJson};
let messaging = null;

function ensureFirebaseMessaging() {
    if (!firebaseConfig || !firebaseConfig.apiKey) {
        return null;
    }

    if (!firebase.apps.length) {
        firebase.initializeApp(firebaseConfig);
    }

    if (!messaging) {
        messaging = firebase.messaging();
    }

    return messaging;
}

ensureFirebaseMessaging();

self.addEventListener('message', function (event) {
    if (event.data?.type !== 'FIREBASE_CONFIG' || !event.data?.config?.apiKey) {
        return;
    }

    firebaseConfig = event.data.config;
    ensureFirebaseMessaging();
});

const backgroundMessaging = ensureFirebaseMessaging();

if (backgroundMessaging) {
    backgroundMessaging.onBackgroundMessage(function (payload) {
        const data = payload?.data || {};
        const notification = payload?.notification || {};
        const title = notification.title || data.title || 'New notification';
        const body = notification.body || data.description || data.content || 'You have a new notification';
        const url = data.url || '/';

        self.registration.showNotification(title, {
            body,
            icon: data.icon || '/assets/favicon.png',
            badge: data.icon || '/assets/favicon.png',
            data: {
                url,
            },
        });
    });
}

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const targetUrl = event.notification?.data?.url || '/';

    event.waitUntil(
        clients.matchAll({
            type: 'window',
            includeUncontrolled: true,
        }).then(function (clientList) {
            for (const client of clientList) {
                if ('focus' in client) {
                    client.navigate(targetUrl);
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(targetUrl);
            }
        })
    );
});
JS;

    return response($content, 200)
        ->header('Content-Type', 'text/javascript')
        ->header('Service-Worker-Allowed', '/');
});

// product details page needs server-rendered og:title/og:image for link previews (crawlers don't run JS)
Route::get('/products/{slug}/details', function (string $slug) {
    $product = Product::isActive()->where('slug', $slug)->first();

    return view('app', ['product' => $product]);
});

// handle frontend page load
Route::get('/{any}', function () {

    // manage admin and shop routes
    if (request()->is('admin/*', 'shop/*')) {
        return abort(404);
    }

    return view('app');
})->where('any', '.*');
