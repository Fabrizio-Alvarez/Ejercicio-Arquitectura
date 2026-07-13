<?php

use App\Http\Controllers\Api\CatalogoController;
use App\Http\Controllers\Api\AjusteController;
use App\Http\Controllers\Api\CierreDeCajaController;
use App\Http\Controllers\Api\CobroController;
use App\Http\Controllers\Api\ReabastecimientoController;
use App\Http\Controllers\Api\ReposicionController;
use App\Http\Controllers\Api\StockController;
use App\Http\Controllers\Api\TokenController;
use Illuminate\Support\Facades\Route;

// Login de API: emite un token de Sanctum. Público.
Route::post('/tokens', TokenController::class);

// Endpoints protegidos: requieren autenticación (sesión SPA o token Bearer)
// y el rol del usuario debe coincidir con el de cada ruta.
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/checkout', CobroController::class)->middleware('rol:cajero');
    Route::get('/cash-close', CierreDeCajaController::class)->middleware('rol:cajero');
    Route::get('/stock', StockController::class)->middleware('rol:repositor');
    Route::post('/replenish/{productId}', ReposicionController::class)->middleware('rol:repositor');
    Route::post('/restock/{productId}', ReabastecimientoController::class)->middleware('rol:depositista');
    Route::get('/products', [CatalogoController::class, 'productos'])->middleware('rol:depositista');
    Route::post('/products', [CatalogoController::class, 'crearProducto'])->middleware('rol:depositista');
    Route::put('/products/{id}', [CatalogoController::class, 'actualizarProducto'])->middleware('rol:depositista');
    Route::delete('/products/{id}', [CatalogoController::class, 'eliminarProducto'])->middleware('rol:depositista');
    Route::post('/offers', [CatalogoController::class, 'crearOferta'])->middleware('rol:depositista');
    Route::delete('/offers/{id}', [CatalogoController::class, 'eliminarOferta'])->middleware('rol:depositista');
    Route::post('/adjust/{productId}', AjusteController::class)->middleware('rol:depositista');
});
