<?php

use App\Http\Controllers\Api\CierreDeCajaController;
use App\Http\Controllers\Api\CobroController;
use App\Http\Controllers\Api\ReposicionController;
use App\Http\Controllers\Api\StockController;
use Illuminate\Support\Facades\Route;

Route::post('/checkout', CobroController::class);
Route::get('/cash-close', CierreDeCajaController::class);
Route::get('/stock', StockController::class);
Route::post('/replenish/{productId}', ReposicionController::class);
