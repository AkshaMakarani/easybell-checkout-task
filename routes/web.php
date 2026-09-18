<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CheckoutController;

Route::get('/', function () {
    return redirect()->route('checkout.index');
});

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::post('/checkout/scan', [CheckoutController::class, 'scan'])->name('checkout.scan');
Route::post('/checkout/void', [CheckoutController::class, 'void'])->name('checkout.void');
Route::post('/checkout/reset', [CheckoutController::class, 'reset'])->name('checkout.reset');
?>
