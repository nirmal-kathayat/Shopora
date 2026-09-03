<?php

use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| The storefront (Next.js) talks to the shop through these routes using
| Sanctum bearer tokens. The admin panel is unaffected - it keeps using the
| session guard on routes/web.php.
|
*/

Route::prefix('auth')->group(function () {
    Route::post('register', [CustomerAuthController::class, 'register'])->middleware('throttle:10,1');
    Route::post('login', [CustomerAuthController::class, 'login'])->middleware('throttle:20,1');

    Route::middleware(['auth:sanctum', 'abilities:customer'])->group(function () {
        Route::get('me', [CustomerAuthController::class, 'me']);
        Route::post('logout', [CustomerAuthController::class, 'logout']);
        Route::post('logout-all', [CustomerAuthController::class, 'logoutAll']);
    });
});

// The signed-in customer's own record.
Route::prefix('account')->middleware(['auth:sanctum', 'abilities:customer'])->group(function () {
    Route::get('profile', [AccountController::class, 'show']);
    Route::put('profile', [AccountController::class, 'update']);
    Route::post('photo', [AccountController::class, 'uploadPhoto']);
    Route::delete('photo', [AccountController::class, 'deletePhoto']);
});

// The signed-in customer's cart.
Route::prefix('cart')->middleware(['auth:sanctum', 'abilities:customer'])->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'store']);
    Route::post('merge', [CartController::class, 'merge']);
    Route::put('{productId}', [CartController::class, 'updateQty'])->whereNumber('productId');
    Route::delete('{productId}', [CartController::class, 'destroy'])->whereNumber('productId');
});

// The signed-in customer's saved products.
Route::prefix('wishlist')->middleware(['auth:sanctum', 'abilities:customer'])->group(function () {
    Route::get('/', [WishlistController::class, 'index']);
    Route::post('/', [WishlistController::class, 'store']);
    Route::delete('{productId}', [WishlistController::class, 'destroy'])->whereNumber('productId');
});

// The public catalogue.
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');
Route::get('products/{id}/reviews', [ProductReviewController::class, 'index'])->whereNumber('id');
Route::post('products/{id}/reviews', [ProductReviewController::class, 'store'])
    ->whereNumber('id')
    ->middleware(['auth:sanctum', 'abilities:customer']);

// Public homepage content - no token, this is what a first-time visitor sees.
Route::prefix('home')->group(function () {
    Route::get('hero', [HomeController::class, 'hero']);
    Route::get('deals', [HomeController::class, 'deals']);
    Route::get('categories', [HomeController::class, 'categories']);
    Route::get('product-trust', [HomeController::class, 'productTrust']);
});
