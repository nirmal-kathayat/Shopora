<?php

use App\Http\Controllers\Api\CustomerAuthController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ProductController;
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

// The public catalogue.
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show'])->whereNumber('id');

// Public homepage content - no token, this is what a first-time visitor sees.
Route::prefix('home')->group(function () {
    Route::get('hero', [HomeController::class, 'hero']);
    Route::get('deals', [HomeController::class, 'deals']);
    Route::get('categories', [HomeController::class, 'categories']);
    Route::get('product-trust', [HomeController::class, 'productTrust']);
});
