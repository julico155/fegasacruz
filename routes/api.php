<?php


// routes/api.php

use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

// Este endpoint será llamado por TU API EXTERNA, no por PagoFácil directamente.
Route::post('/pagofacil/callback', [VentaController::class, 'urlCallback'])->name('pagofacil.callback');

// Ruta para que el frontend consulte el estado de la transacción (polling)
Route::post('/consultartransaccion', [VentaController::class, 'ConsultarEstado'])->name('consultar.estado');

// Si necesitas un endpoint para que el frontend actualice el estado de la venta
// (aunque el urlCallback debería ser el principal), podrías tener algo así:
// Route::post('/actualizar-estado-venta', [VentaController::class, 'actualizarEstadoVenta']);