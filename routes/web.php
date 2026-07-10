<?php

use App\Facades\Perfil;
use App\Http\Controllers\Web\PaginaWebController;
use Illuminate\Support\Facades\Route;

// Selector de perfil (público, sin perfil requerido).
Route::get('/iniciar', [PaginaWebController::class, 'iniciar'])->name('iniciar');
Route::post('/iniciar', [PaginaWebController::class, 'establecerPerfil'])->name('perfil.establecer');
Route::post('/salir', [PaginaWebController::class, 'salir'])->name('perfil.salir');

// Vistas del supermercado, gateadas por perfil.
Route::middleware('perfil')->group(function () {
    Route::get('/', fn () => redirect()->route(Perfil::actual()->paginas()[0]['ruta']))->name('inicio');
    Route::get('/cobrar', [PaginaWebController::class, 'cobrar'])->name('cobrar');
    Route::get('/stock', [PaginaWebController::class, 'stock'])->name('stock');
    Route::get('/movimientos', [PaginaWebController::class, 'movimientos'])->name('movimientos');
});
