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

Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/eliminados', [HomeController::class, 'eliminados'])
        ->name('eliminados.index');
});

Route::patch('/clientes/{id}/restore', [ClienteController::class, 'restore'])
    ->name('clientes.restore');

Route::patch('/ventas/{id}/restore', [VentaController::class, 'restore'])
    ->name('ventas.restore');

Route::patch('/facturas/{id}/restore', [FacturaController::class, 'restore'])
    ->name('facturas.restore');

#Route::middleware('auth')->group(function () {
#    Route::resource('clientes', ClienteController::class);
#    Route::resource('ventas', VentaController::class);
#    Route::resource('facturas', FacturaController::class);
#});
Route::get('/clientes/{cliente}/ventas', function ($clienteId) {
    return \App\Models\Venta::where('cliente_id', $clienteId)
        ->whereNull('deleted_at') // si usas soft deletes
        ->get();
})->middleware('auth');


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
    Route::delete('/clientes/{cliente}', [ClienteController::class, 'destroy'])
        ->name('clientes.destroy');

    Route::delete('/ventas/{venta}', [VentaController::class, 'destroy'])
        ->name('ventas.destroy');

    Route::delete('/facturas/{factura}', [FacturaController::class, 'destroy'])
        ->name('facturas.destroy');
});

Route::patch('/clientes/{cliente}/restore', [ClienteController::class, 'restore'])
    ->middleware(['auth', 'role:admin'])
    ->name('clientes.restore');

Route::get('/clientes-eliminados', [ClienteController::class, 'eliminados'])
    ->middleware(['auth', 'role:admin'])
    ->name('clientes.eliminados');

Route::get('/export/ingresos-por-mes', [HomeController::class, 'exportIngresosPorMes'])
    ->name('export.ingresos');

Route::get('/export/flujo-caja', [HomeController::class, 'exportFlujoCajaMensual'])
    ->name('export.flujo');

require __DIR__ . '/auth.php';
