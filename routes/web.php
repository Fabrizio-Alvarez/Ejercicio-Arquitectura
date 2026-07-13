<?php

use App\Facades\Perfil;
use App\Http\Controllers\Web\PaginaWebController;
use Illuminate\Support\Facades\Route;

// Auth (login web con roles). Público, sin perfil requerido.
Route::get('/login', [PaginaWebController::class, 'login'])->name('login')->middleware('guest');
Route::post('/login', [PaginaWebController::class, 'autenticar'])->name('login.attempt')->middleware('guest');
Route::post('/logout', [PaginaWebController::class, 'cerrarSesion'])->name('logout');

// Vistas del supermercado, gateadas por perfil.
Route::middleware('perfil')->group(function () {
    Route::get('/', fn () => redirect()->route(Perfil::actual()->paginas()[0]['ruta']))->name('inicio');
    Route::get('/cobrar', [PaginaWebController::class, 'cobrar'])->name('cobrar');
    Route::get('/cierre', [PaginaWebController::class, 'cierre'])->name('cierre');
    Route::get('/stock', [PaginaWebController::class, 'stock'])->name('stock');
    Route::get('/movimientos', [PaginaWebController::class, 'movimientos'])->name('movimientos');
    Route::get('/alertas', [PaginaWebController::class, 'alertas'])->name('alertas');
});
