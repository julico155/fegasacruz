<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Admin\ProductoController;
use App\Http\Controllers\Admin\CategoriaController; // Importa tu controlador de categorías
use App\Http\Controllers\Admin\InventarioController;
use App\Http\Controllers\VentaController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\DashboardController;


Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});


Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/shop', [VentaController::class, 'shop'])->name('shop.index');
Route::post('/cart/add', [VentaController::class, 'addToCart'])->name('cart.add');
Route::post('/cart/update', [VentaController::class, 'updateCart'])->name('cart.update');
Route::post('/cart/remove', [VentaController::class, 'removeFromCart'])->name('cart.remove');
Route::get('/cart', [VentaController::class, 'showCart'])->name('cart.show');


Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');



    Route::get('/search', [SearchController::class, 'globalSearch'])->name('global.search');
    Route::post('/checkout', [VentaController::class, 'checkout'])->name('checkout');

    Route::get('/my-purchases', [VentaController::class, 'mySales'])->name('my.sales.index');
    
});

Route::middleware('auth')->prefix('admin')->name('admin.')->group(function () {
    // ¡AHORA SÍ! Rutas para el CRUD de Productos y Categorías están dentro de este grupo
    // Esto las hace accesibles en /admin/productos y /admin/categorias
    // Y sus nombres de ruta serán admin.productos.* y admin.categorias.*
    Route::resource('productos', ProductoController::class);
    Route::resource('categorias', CategoriaController::class);
    Route::resource('inventario', InventarioController::class);

    Route::resource('sales', VentaController::class)->only(['index', 'show'])->parameters([
        'sales' => 'venta' // Mapea 'sales' (nombre del recurso) a 'venta' (nombre del parámetro en el método)
    ]);
});


require __DIR__.'/auth.php';
