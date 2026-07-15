<?php

use App\Facades\Perfil;
use App\Http\Controllers\Web\TiendaController;
use Illuminate\Support\Facades\Route;

// Storefront público (e-commerce). Sin auth requerida.
Route::prefix('/tienda')->name('tienda.')->group(function () {
    Route::get('/', [TiendaController::class, 'inicio'])->name('inicio');
    Route::get('/catalogo', [TiendaController::class, 'catalogo'])->name('catalogo');
    Route::get('/producto/{id}', [TiendaController::class, 'producto'])->name('producto');
    Route::get('/checkout', [TiendaController::class, 'checkout'])->name('checkout');
    Route::post('/checkout', [TiendaController::class, 'confirmar'])->name('confirmar');
    Route::get('/confirmacion/{ventaId}', [TiendaController::class, 'confirmacion'])->name('confirmacion');
});

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
    Route::get('/tablero', [PaginaWebController::class, 'tablero'])->name('tablero');
    Route::get('/catalogo', [PaginaWebController::class, 'catalogo'])->name('catalogo');
    Route::get('/auditoria', [PaginaWebController::class, 'auditoria'])->name('auditoria');
    Route::get('/reportes', [PaginaWebController::class, 'reportes'])->name('reportes');
});
