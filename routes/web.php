<?php
/**
 * @Author: Bernard Hanna
 * @Date:   2023-04-07 10:12:53
 * @Last Modified by:   Bernard Hanna
 * @Last Modified time: 2023-05-24 11:38:30
 */

use App\Http\Controllers\CustomAuthController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ShopifyController;
use App\Http\Controllers\CreateOrderController;
use App\Http\Controllers\OrderDetailsController;
use App\Http\Controllers\ShopifyProductController;
use App\Http\Controllers\ShopifyFulfillmentController;
use App\Http\Controllers\OrderDispatchController;
use App\Http\Controllers\GarbageCollectionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CustomAuthController::class, 'login'])->middleware('alreadyLoggedIn');
Route::get('/login', [CustomAuthController::class, 'login'])->name('login')->middleware('alreadyLoggedIn');
Route::get('/register', [CustomAuthController::class, 'register'])->name('register')->middleware('alreadyLoggedIn');
Route::post('/register-user', [CustomAuthController::class, 'registerUser'])->name('register-user');
Route::post('login-user', [CustomAuthController::class, 'loginUser'])->name('login-user');
Route::get('/logout', [CustomAuthController::class, 'logout']);
Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
Route::post('/orders/sync', [OrderController::class, 'syncOrders'])->name('orders.sync');
Route::post('/orders/sendOrdersToShopify', [CreateOrderController::class, 'sendOrdersToShopify'])->name('orders.sendOrdersToShopify');
Route::get('/order-details/{order_id}', 'App\Http\Controllers\OrderDetailsController@show')->name('order-details');
Route::post('/sync-shopify-products', [App\Http\Controllers\ShopifyProductController::class, 'syncShopifyProducts'])->name('sync-shopify-products');
Route::post('/sync-fulfillments', [App\Http\Controllers\ShopifyFulfillmentController::class, 'syncFulfillments'])->name('sync-fulfillments');
Route::middleware(['isLoggedIn'])->group(function () {
    Route::get('/dashboard', [CustomAuthController::class, 'dashboard'])->name('dashboard');
    Route::get('/dashboard/orders', [OrderController::class, 'index'])->name('dashboard.orders');
});
Route::get('/dashboard', [OrderController::class, 'showOrders'])->name('orders.show');
Route::get('adminer', function () {
    return \File::get(public_path('adminer.php'));
})->middleware('adminer');

Route::post('/sync-cancelled-orders', 'App\Http\Controllers\ShopifyFulfillmentController@syncCancelledOrders')->name('sync-cancelled-orders');
Route::post('/garbage-collection', [GarbageCollectionController::class, 'garbageCollection'])->name('garbage-collection');

