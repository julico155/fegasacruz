<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Venta;
use App\Models\DetalleVenta;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Visit;
use GuzzleHttp\Client;

class VentaController extends Controller
{
    // Credenciales de PagoFácil como propiedades de la clase
    private $tokenService = '51247fae280c20410824977b0781453df59fad5b23bf2a0d14e884482f91e09078dbe5966e0b970ba696ec4caf9aa5661802935f86717c481f1670e63f35d5041c31d7cc6124be82afedc4fe926b806755efe678917468e31593a5f427c79cdf016b686fca0cb58eb145cf524f62088b57c6987b3bb3f30c2082b640d7c52907';
    private $tokenSecret = '9E7BC239DDC04F83B49FFDA5';
    public $tcCommerceID = "d029fa3a95e174a19934857f535eb9427d967218a36ea014b70ad704bc6c8d1c";

    private $httpClient;

    public function __construct()
    {
        $this->httpClient = new Client();
    }

    /**
     * Muestra la página principal de la tienda con los productos disponibles.
     * @return \Inertia\Response
     */
    public function shop()
    {
        $productos = Producto::with('categoria')->latest()->paginate(12);

        $visitableId = 10;
        $visitableType = 'App\\Models\\Shop_IndexPage';

        $visit = Visit::firstOrCreate(
            ['visitable_type' => $visitableType, 'visitable_id' => $visitableId],
            ['count' => 0]
        );
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Shop/Index', [
            'productos' => $productos,
            'pageVisits' => $pageVisits,
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
     * Este método ahora devuelve un JSON con el QR, como espera tu frontend.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse
     */
    public function checkout(Request $request)
    {
        Log::info('Inicio del checkout.');
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            Log::warning('Intento de checkout con carrito vacío.');
            return redirect()->back()->with('error', 'El carrito está vacío.');
        }

        if (!Auth::check()) {
            Log::warning('Usuario no autenticado intentó checkout.');
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para completar tu compra.');
        }

        $user = Auth::user();

        // Validar si el usuario tiene un número de teléfono para PagoFácil
        if (empty($user->telefono)) {
             Log::warning('Usuario autenticado sin número de teléfono para checkout.', ['user_id' => $user->id]);
             return redirect()->back()->with('error', 'Por favor, actualiza tu perfil con un número de teléfono para completar la compra.');
        }
        // Asumimos un valor fijo para CI/NIT si no está en el usuario. ¡AJUSTAR!
        $ciNit = '1234567'; // <-- ¡AJUSTAR! Si tienes un campo 'ci_nit' en tu tabla 'users', usa $user->ci_nit

        DB::beginTransaction();

        try {
            $totalVenta = 0;
            $detallesVenta = [];

            foreach ($cart as $item) {
                $producto = Producto::find($item['id']);
                if (!$producto || $producto->stock_actual < $item['cantidad']) {
                    DB::rollBack();
                    Log::error('Stock insuficiente durante el checkout.', ['producto_id' => $item['id'], 'cantidad_solicitada' => $item['cantidad'], 'stock_actual' => $producto->stock_actual ?? 0]);
                    return redirect()->back()->with('error', 'Stock insuficiente para ' . $item['nombre'] . '. Stock disponible: ' . ($producto->stock_actual ?? 0));
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
                'user_id' => $user->id,
                // Si 'cliente_id' es obligatorio en 'ventas' y quieres usar user_id para ello, descomenta:
                // 'cliente_id' => $user->id,
                'fecha_venta' => now(),
                'total' => $totalVenta,
                'estado' => 'pendiente_pago',
                'transaccion_id_pagofacil' => null, // Se llenará después del callback, o se omite si no se guarda.
            ]);
            Log::info('Venta creada en DB con estado pendiente.', ['venta_id' => $venta->id, 'total' => $venta->total]);

            foreach ($detallesVenta as $detalle) {
                $venta->detalles()->create($detalle);
                $producto = Producto::find($detalle['producto_id']);
                $producto->stock_actual -= $detalle['cantidad'];
                $producto->save();
            }

            // --- INTEGRACIÓN CON PAGOFÁCIL ---
            $accessToken = $this->obtenerTokenPagoFacil();

            // tcUrlCallBack: URL a la que PagoFácil enviará la notificación POST.
            // Esta es la URL de TU API EXTERNA que actuará como puente.
            $urlCallback = "https://660aff83-67cc-4ace-b5e8-96b77d1f78a0-00-29jcyigh7qnyh.janeway.replit.dev/api/urlcallback";

            // tcUrlReturn: URL a la que PagoFácil redirigirá el NAVEGADOR del cliente.
            // Esta es una URL de tu aplicación Laravel.
            $urlReturn = env('APP_URL') . route('venta.pagoExitoso', [], false);

            Log::info('URLs de PagoFácil enviadas:', ['callback' => $urlCallback, 'return' => $urlReturn]);

            // Construcción correcta de taPedidoDetalle
            $taPedidoDetalle = array_map(function ($item) {
                return [
                    "Serial" => (string)$item['id'],
                    "Producto" => $item['nombre'],
                    "Cantidad" => $item['cantidad'],
                    "Precio" => (float)number_format($item['precio_unitario'], 2, '.', ''),
                    "Descuento" => 0,
                    "Total" => (float)number_format($item['cantidad'] * $item['precio_unitario'], 2, '.', ''),
                ];
            }, $cart);

            $pagoFacilData = [
                "tcCommerceID" => $this->tcCommerceID,
                "tcNroPago" => "ORDEN-" . $venta->id . "-" . uniqid(), // ID único para PagoFácil
                "tcNombreUsuario" => $user->name,
                "tnCiNit" => (int)$ciNit, // Usar el CI/NIT del usuario o el valor fijo
                "tnTelefono" => (int)$user->telefono,
                "tcCorreo" => $user->email,
                "tnMontoClienteEmpresa" => '0.01',
                "tnMoneda" => 2, // 2 para Bolivianos
                "tcUrlCallBack" => $urlCallback,
                "tcUrlReturn" => $urlReturn,
                "taPedidoDetalle" => $taPedidoDetalle,
            ];

            Log::info('Datos enviados a PagoFácil:', $pagoFacilData);

            $urlPagoQR = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/pagoqr";
            $response = $this->httpClient->post($urlPagoQR, [
                'headers' => ['Accept' => 'application/json', 'Authorization' => 'Bearer ' . $accessToken, 'Content-Type' => 'application/json'],
                'json' => $pagoFacilData
            ]);

            $responseBody = $response->getBody()->getContents();
            Log::info('Respuesta RAW de PagoFácil (Body):', ['body' => $responseBody]);

            $result = json_decode($responseBody, true);
            Log::info('Resultado decodificado de PagoFácil:', ['result' => $result]);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($result['values'])) {
                Log::error('Error decodificando o campo "values" ausente de PagoFácil.', ['result' => $result, 'json_error' => json_last_error_msg()]);
                throw new \Exception("Respuesta inesperada de PagoFácil.");
            }

            $valuesParts = explode(";", $result['values']);
            Log::info('Partes de "values" de PagoFácil:', ['valuesParts' => $valuesParts]);

            if (count($valuesParts) < 2) {
                Log::error('Formato de "values" inesperado de PagoFácil.', ['values' => $result['values']]);
                throw new \Exception("Formato de respuesta QR inesperado de PagoFácil.");
            }

            $nroTransaccionPagoFacil = $valuesParts[0];
            $qrData = json_decode($valuesParts[1], true);
            Log::info('Datos del QR decodificados:', ['qrData' => $qrData]);

            if (json_last_error() !== JSON_ERROR_NONE || !isset($qrData['qrImage'])) {
                Log::error('Error decodificando QR o imagen no encontrada.', ['qr_data' => $qrData, 'json_error' => json_last_error_msg()]);
                throw new \Exception("No se pudo obtener la imagen del código QR.");
            }

            $qrImageBase64 = "data:image/png;base64," . $qrData['qrImage'];

            // Omitimos guardar la transaccion_id_pagofacil si no es relevante para tu proyecto de prueba
            // $venta->transaccion_id_pagofacil = $nroTransaccionPagoFacil;
            // $venta->save();

            session()->forget('cart'); // Vaciar el carrito

            DB::commit(); // Confirmar la transacción de la DB

            // --- ¡CAMBIO CLAVE! DEVOLVER JSON, NO REDIRIGIR A VISTA INERTIA ---
            return response()->json([
                'success' => true,
                'qrImageBase64' => $qrImageBase64,
                'nroTransaccion' => $nroTransaccionPagoFacil,
                'ventaId' => $venta->id, // Pasa el ID de la venta para que el frontend lo use en el polling
            ]);

        } catch (\Exception $e) {
            DB::rollBack(); // Revertir la transacción de la DB si algo falla
            Log::error('Error durante el checkout con PagoFácil: ' . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            // Devolver un JSON de error para que el frontend lo maneje
            return response()->json(['error' => 'Error al procesar el pago: ' . $e->getMessage()], 500);
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
     */
    public function show(Venta $venta)
    {
        $venta->load(['user', 'detalles.producto']);

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
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'Debes iniciar sesión para ver tus compras.');
        }

        $ventas = Venta::where('user_id', Auth::id())
                        ->with(['detalles.producto'])
                        ->latest('fecha_venta')
                        ->paginate(10);

        return Inertia::render('MySales/Index', [
            'ventas' => $ventas,
        ]);
    }

    /**
     * Consulta el estado de una transacción de PagoFácil.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function ConsultarEstado(Request $request)
    {
        try {
            $lnTransaccion = $request->input('tnTransaccion'); // Asegúrate de usar input()

            $lcUrlEstadoTransaccion = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/consultartransaccion";

            $laHeaderEstadoTransaccion = [
                'Accept' => 'application/json'
            ];

            $laBodyEstadoTransaccion = [
                "TransaccionDePago" => $lnTransaccion
            ];

            $loEstadoTransaccion = $this->httpClient->post($lcUrlEstadoTransaccion, [
                'headers' => $laHeaderEstadoTransaccion,
                'json' => $laBodyEstadoTransaccion
            ]);

            $laResultEstadoTransaccion = json_decode($loEstadoTransaccion->getBody()->getContents(), true); // Decodificar como array

            // Verificar si 'values' y 'messageEstado' existen antes de intentar acceder
            $messageEstado = $laResultEstadoTransaccion['values']['messageEstado'] ?? 'Estado desconocido';
            $estadoTransaccion = $laResultEstadoTransaccion['values']['EstadoTransaccion'] ?? null; // Obtener el código de estado (ej. 2 para pagado)

            return response()->json([
                'message' => $messageEstado,
                'estadoTransaccion' => $estadoTransaccion, // Devolver el código de estado
                'full_response' => $laResultEstadoTransaccion
            ]);

        } catch (\Throwable $th) {
            Log::error('Error en ConsultarEstado', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile()
            ]);
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    /**
     * Obtiene el token de autenticación de PagoFácil.
     * Es un método privado porque solo se usa internamente.
     * @return string El token de acceso.
     * @throws \Exception Si ocurre un error al obtener el token.
     */
    private function obtenerTokenPagoFacil() // Renombrado de obtenerToken()
    {
        try {
            $url = "https://serviciostigomoney.pagofacil.com.bo/api/servicio/login";
            $body = [
                'TokenService' => $this->tokenService, // Usar propiedad de clase
                'TokenSecret' => $this->tokenSecret   // Usar propiedad de clase
            ];

            $headers = ['Accept' => 'application/json'];

            $response = $this->httpClient->post($url, [
                'headers' => $headers,
                'json' => $body
            ]);

            $result = json_decode($response->getBody()->getContents(), true);

            if (!isset($result["values"])) {
                Log::error('PagoFacil: No se pudo obtener un token válido.', ['response' => $result]);
                throw new \Exception("PagoFacil: No se pudo obtener un token válido para el pago.");
            }

            return $result["values"];
        } catch (\Throwable $th) {
            Log::error('PagoFacil: Error al obtener el token: ' . $th->getMessage());
            throw new \Exception("Error al obtener el token: " . $th->getMessage());
        }
    }

    /**
     * Endpoint para el callback de PagoFácil.
     * ¡IMPORTANTE! Este método SÓLO será llamado por TU API EXTERNA.
     * PagoFácil NO lo llamará directamente.
     * Tu API externa debe recibir el callback de PagoFácil y LUEVO llamar a este endpoint.
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function urlCallback(Request $request)
    {
        Log::info('Callback de PagoFácil RECIBIDO por mi Laravel (desde API externa)', ['data' => $request->all()]);

        try {
            // Se espera que la API externa reenvíe estos datos.
            $tcNroPago = $request->input('Referencia'); // El número de pago que enviaste a PagoFácil
            $estadoPagoFacil = $request->input('Estado'); // El estado de la transacción (2=Pagado)
            $idTransaccionPagoFacil = $request->input('IdTransaccion'); // ID de transacción de PagoFácil

            // Extraer el ID de venta de tu campo tcNroPago (ej: "ORDEN-IDVENTA-RANDOM")
            preg_match('/ORDEN-(\d+)-/', $tcNroPago, $matches);
            $ventaId = $matches[1] ?? null;

            $venta = null;
            if ($ventaId) {
                $venta = Venta::find($ventaId);
            }

            if (!$venta) {
                Log::error('Callback: Venta no encontrada o ID no extraído', ['referencia' => $tcNroPago, 'venta_id_extraido' => $ventaId, 'callback_data' => $request->all()]);
                return response()->json(['error' => 0, 'status' => 1, 'message' => "Venta no encontrada, pero callback recibido."]);
            }

            if ($estadoPagoFacil == 2) { // '2' es el estado de "Pagado" en PagoFácil
                $venta->estado = 'pagado';
                // Si tienes un campo para el ID de transacción de PagoFácil y quieres guardarlo aquí:
                // $venta->transaccion_id_pagofacil = $idTransaccionPagoFacil;
                $venta->save();
                Log::info('Venta actualizada a Pagada por callback (via API externa)', ['venta_id' => $venta->id, 'transaccion_pf' => $idTransaccionPagoFacil]);
            } else {
                $venta->estado = 'fallido'; // o 'expirado', 'rechazado'
                $venta->save();
                Log::warning('Callback con estado no pagado (via API externa)', ['venta_id' => $venta->id, 'estado_pf' => $estadoPagoFacil, 'transaccion_pf' => $idTransaccionPagoFacil]);
            }

            // Responder OK para que la API externa sepa que procesaste el callback
            return response()->json(['error' => 0, 'status' => 1, 'message' => "Callback procesado correctamente por Laravel."]);

        } catch (\Throwable $th) {
            Log::error('Error en urlCallback de VentaController (llamado por API externa)', [
                'error' => $th->getMessage(),
                'line' => $th->getLine(),
                'file' => $th->getFile(),
                'trace' => $th->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return response()->json(['error' => 1, 'status' => 0, 'messageSistema' => "[TRY/CATCH] " . $th->getMessage(), 'message' => "Error al procesar el callback en tu sistema."]);
        }
    }

    /**
     * Vista a la que se redirige al usuario tras un pago exitoso (no es un callback).
     * @return \Inertia\Response
     */
    public function pagoExitoso() // Renombrado de pagado()
    {
        return Inertia::render('Shop/PaymentSuccess');
    }


    public function actualizarEstadoVentaPorPolling(Request $request)
    {
        $request->validate([
            'venta_id' => 'required|exists:ventas,id',
            // Puedes añadir validación para el estado si quieres que el frontend lo envíe
            // 'estado' => 'required|string|in:pagado,completado,fallido',
        ]);

        $ventaId = $request->input('venta_id');
        // $nuevoEstado = $request->input('estado', 'completado'); // Si el frontend envía el estado

        try {
            $venta = Venta::find($ventaId);

            if (!$venta) {
                Log::error('Actualizar Estado Polling: Venta no encontrada.', ['venta_id' => $ventaId]);
                return response()->json(['success' => false, 'message' => 'Venta no encontrada.'], 404);
            }

            // Aquí actualizamos el estado. Asumimos 'completado' porque el polling ya confirmó el pago exitoso.
            // Si quieres que dependa del estado devuelto por PagoFácil, deberías pasarlo como parámetro
            // desde el frontend y validar aquí.
            $venta->estado = 'completado'; // O 'pagado', según tu preferencia
            // Si quieres guardar el ID de transacción de PagoFácil aquí (si no lo hiciste en checkout):
            // $venta->transaccion_id_pagofacil = $request->input('nroTransaccion');
            $venta->save();

            Log::info('Venta actualizada a "completado" por polling.', ['venta_id' => $venta->id]);
            return response()->json(['success' => true, 'message' => 'Estado de venta actualizado correctamente.']);

        } catch (\Throwable $th) {
            Log::error('Error al actualizar estado de venta por polling:', [
                'error' => $th->getMessage(),
                'venta_id' => $ventaId,
                'file' => $th->getFile(),
                'line' => $th->getLine()
            ]);
            return response()->json(['success' => false, 'message' => 'Error al actualizar el estado de la venta: ' . $th->getMessage()], 500);
        }
    }
    
}