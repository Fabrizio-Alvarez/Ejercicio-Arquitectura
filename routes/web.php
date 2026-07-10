<?php

use App\Http\Controllers\Web\PaginaWebController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect('/stock'));

Route::get('/stock', [PaginaWebController::class, 'stock'])->name('stock');
Route::get('/cobrar', [PaginaWebController::class, 'cobrar'])->name('cobrar');
Route::get('/movimientos', [PaginaWebController::class, 'movimientos'])->name('movimientos');
