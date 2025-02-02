<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', [\App\Http\Controllers\ProductController::class, 'index'])->name('dashboard');
Route::get('/products/{product:slug}', [\App\Http\Controllers\ProductController::class, 'show'])->name('product.show');

Route::post('/stripe/webhook', [\App\Http\Controllers\StripeController::class, 'webhook'])->name('stripe.webhook');


Route::controller(CartController::class)->group(function () {
   Route::get('/cart', 'index')->name('cart.index');
   Route::post('/cart/{product}', 'store')->name('cart.store');
   Route::put('/cart/{product}', 'update')->name('cart.update');
   Route::delete('/cart/{product}', 'destroy')->name('cart.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware(['verified'])->group(function () {
        Route::post('/checkout', [CartController::class, 'checkout'])->name('cart.checkout');
        Route::get('stripe/success', [\App\Http\Controllers\StripeController::class, 'success'])->name('stripe.success');
        Route::get('stripe/cancel', [\App\Http\Controllers\StripeController::class, 'cancel'])->name('stripe.cancel');
    });
});

require __DIR__.'/auth.php';
