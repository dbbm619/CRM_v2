<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\FacturaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('auth.login');
});

#Route::middleware('auth')->group(function () {
#    Route::resource('clientes', ClienteController::class);
#    Route::resource('ventas', VentaController::class);
#    Route::resource('facturas', FacturaController::class);
#});

Route::middleware(['auth'])->group(function () {
    Route::resource('clientes', ClienteController::class)
        ->except(['destroy']); // removemos destroy para ponerlo con role
    Route::resource('ventas', VentaController::class)
        ->except(['destroy']);
    Route::resource('facturas', FacturaController::class)
        ->except(['destroy']);
});

#Route::get('/dashboard', function () {
 #   return view('home');
#})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::get('/home', [HomeController::class, 'index'])->name('home');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::delete('/clientes/{id}', [ClienteController::class, 'destroy'])->name('clientes.destroy');
    Route::delete('/ventas/{id}', [VentaController::class, 'destroy'])->name('ventas.destroy');
    Route::delete('/facturas/{id}', [FacturaController::class, 'destroy'])->name('facturas.destroy');
});


require __DIR__.'/auth.php';
