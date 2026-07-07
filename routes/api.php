<?php

use App\Http\Controllers\Api\CashCloseController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ReplenishmentController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

Route::post('/checkout', CheckoutController::class);
Route::get('/cash-close', CashCloseController::class);
Route::get('/stock', StockController::class);
Route::post('/replenish/{productId}', ReplenishmentController::class);
