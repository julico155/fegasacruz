<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Importa la fachada Log
use App\Models\Visit;
class VentaController extends Controller
{
    /**
     * Muestra la página principal de la tienda con los productos disponibles.
     * @return \Inertia\Response
     */
    public function shop()
    {
        $productos = Producto::with('categoria')->latest()->paginate(12);

        // Lógica para el contador de visitas de la página de la tienda (ID 10)
        $visitableId = 10;
        $visitableType = 'App\\Models\\Shop_IndexPage'; // Usamos el mismo tipo que en el middleware anterior
        
        Visit::updateOrCreate(
            [
                'visitable_type' => $visitableType,
                'visitable_id' => $visitableId,
            ],
            [
                'count' => DB::raw('count + 1'), // Incrementa el contador
            ]
        );

        // Obtener el conteo de visitas actualizado para pasarlo a la vista
        $pageVisits = Visit::where('visitable_id', $visitableId)
                               ->where('visitable_type', $visitableType)
                               ->value('count') ?? 0;

        return Inertia::render('Shop/Index', [
            'productos' => $productos,
            'pageVisits' => $pageVisits, // Pasa el conteo de visitas a la vista con el nombre 'pageVisits'
        ]);
    }
    /**
     * Añade un producto al carrito de compras en la sesión.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function addToCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:1',
        ]);

        $producto = Producto::find($request->producto_id);
        $cart = session()->get('cart', []);

        if (isset($cart[$producto->id])) {
            $cart[$producto->id]['cantidad'] += $request->cantidad;
        } else {
            $cart[$producto->id] = [
                "id" => $producto->id,
                "nombre" => $producto->nombre,
                "cantidad" => $request->cantidad,
                "precio_unitario" => $producto->precio_unitario,
                "imagen" => "https://placehold.co/100x100/E0E0E0/000000?text=Producto"
            ];
        }

        session()->put('cart', $cart);

        return redirect()->back()->with('success', 'Producto añadido al carrito.');
    }

    /**
     * Actualiza la cantidad de un producto en el carrito.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
            'cantidad' => 'required|integer|min:0',
        ]);

        $cart = session()->get('cart');

        if (isset($cart[$request->producto_id])) {
            if ($request->cantidad == 0) {
                unset($cart[$request->producto_id]);
            } else {
                $cart[$request->producto_id]['cantidad'] = $request->cantidad;
            }
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Carrito actualizado.');
        }

        return redirect()->back()->with('error', 'Producto no encontrado en el carrito.');
    }

    /**
     * Elimina un producto del carrito.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeFromCart(Request $request)
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,id',
        ]);

        $cart = session()->get('cart');

        if (isset($cart[$request->producto_id])) {
            unset($cart[$request->producto_id]);
            session()->put('cart', $cart);
            return redirect()->back()->with('success', 'Producto eliminado del carrito.');
        }

        return redirect()->back()->with('error', 'Producto no encontrado en el carrito.');
    }

    /**
     * Muestra el contenido del carrito de compras.
     * @return \Inertia\Response
     */
    public function showCart()
    {
        $cart = session()->get('cart', []);
        $total = array_sum(array_map(fn($item) => $item['cantidad'] * $item['precio_unitario'], $cart));

        return Inertia::render('Shop/Cart', [
            'cartItems' => array_values($cart),
            'cartTotal' => $total,
        ]);
    }

    /**
     * Procesa el checkout (pago) y crea la venta.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.show')->with('error', 'El carrito está vacío.');
        }

        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Por favor, inicia sesión para completar tu compra.');
        }

        DB::beginTransaction();

        try {
            $totalVenta = 0;
            $detallesVenta = [];

            foreach ($cart as $item) {
                $producto = Producto::find($item['id']);
                if (!$producto || $producto->stock_actual < $item['cantidad']) {
                    DB::rollBack();
                    return redirect()->route('cart.show')->with('error', 'Stock insuficiente para ' . $item['nombre'] . '. Stock disponible: ' . ($producto->stock_actual ?? 0));
                }
                $subtotal = $item['cantidad'] * $item['precio_unitario'];
                $totalVenta += $subtotal;

                $detallesVenta[] = [
                    'producto_id' => $item['id'],
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $item['precio_unitario'],
                    'subtotal' => $subtotal,
                ];
            }

            $venta = Venta::create([
                'user_id' => Auth::id(),
                'fecha_venta' => now(),
                'total' => $totalVenta,
                'estado' => 'completado',
            ]);

            foreach ($detallesVenta as $detalle) {
                $venta->detalles()->create($detalle);
                
                $producto = Producto::find($detalle['producto_id']);
                $producto->stock_actual -= $detalle['cantidad'];
                $producto->save();
            }

            session()->forget('cart');

            DB::commit();

            return redirect()->route('sales.show', $venta->id)->with('success', '¡Compra realizada con éxito!');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('cart.show')->with('error', 'Error al procesar la compra: ' . $e->getMessage());
        }
    }

    /**
     * Muestra una lista de todas las ventas (para administración).
     * @return \Inertia\Response
     */
    public function index()
    {
        $ventas = Venta::with(['user', 'detalles.producto'])->latest()->paginate(10);
        return Inertia::render('Admin/Sales/Index', [
            'ventas' => $ventas,
        ]);
    }

    /**
     * Muestra los detalles de una venta específica (para administración).
     * @param  \App\Models\Venta  $venta
     * @return \Inertia\Response
     */ public function show(Venta $venta)
    {
        // Cargar las relaciones 'user' y 'detalles.producto'
        // Esto es crucial para que los datos anidados estén disponibles en la vista.
        $venta->load(['user', 'detalles.producto']);
  
        // --- INICIO DE LOGS PARA DEPURACIÓN (Mantener para el siguiente paso) ---
        Log::info('Show Venta - ID de Venta:', ['id' => $venta->id]);
        Log::info('Show Venta - Datos de la venta (toArray):', $venta->toArray());

        if ($venta->user) {
            Log::info('Show Venta - Usuario de la venta:', $venta->user->toArray());
        } else {
            Log::info('Show Venta - Usuario de la venta: NULL o no cargado.');
        }

        if ($venta->detalles) {
            Log::info('Show Venta - Detalles de la venta:', $venta->detalles->toArray());
            foreach ($venta->detalles as $detalle) {
                if ($detalle->producto) {
                    Log::info('Show Venta - Producto en detalle:', $detalle->producto->toArray());
                } else {
                    Log::info('Show Venta - Producto en detalle: NULL o no cargado.');
                }
            }
        } else {
            Log::info('Show Venta - Detalles de la venta: NULL o Vacío.');
        }
        // --- FIN DE LOGS PARA DEPURACIÓN ---

        return Inertia::render('Admin/Sales/Show', [
            'venta' => $venta,
        ]);
    }
    
    /**
     * Muestra las ventas realizadas por el usuario autenticado.
     * @return \Inertia\Response
     */
    public function mySales()
    {
        // Asegúrate de que el usuario esté autenticado
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tus compras.');
        }

        // Obtener las ventas del usuario autenticado, con sus detalles y productos
        $ventas = Venta::where('user_id', Auth::id())
                        ->with(['detalles.producto']) // No necesitamos cargar 'user' aquí, ya es el usuario actual
                        ->latest('fecha_venta')
                        ->paginate(10);

        return Inertia::render('MySales/Index', [
            'ventas' => $ventas,
        ]);
    }

}

