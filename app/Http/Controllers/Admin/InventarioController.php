<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Inventario;
use App\Models\Producto;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use App\Models\Visit; // Importa el modelo Visit

class InventarioController extends Controller
{
    /**
     * Muestra una lista de los movimientos de inventario y registra la visita.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $movimientos = Inventario::with('producto')->latest('fecha')->paginate(10);

        // Lógica para el contador de visitas de la página de índice de Inventario (ID 7)
        $visitableId = 7;
        $visitableType = Inventario::class . '_IndexPage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Inventario/Index', [
            'movimientos' => $movimientos,
            'success' => session('success'),
            'error' => session('error'),
            'pageVisits' => $pageVisits, // Pasa el conteo a la vista
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo movimiento de inventario y registra la visita.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $productos = Producto::select('id', 'nombre', 'stock_actual')->get();

        // Lógica para el contador de visitas de la página de creación de Inventario (ID 8)
        $visitableId = 8;
        $visitableType = Inventario::class . '_CreatePage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Inventario/Create', [
            'productos' => $productos,
            'pageVisits' => $pageVisits, // Pasa el conteo a la vista
        ]);
    }

    /**
     * Almacena un nuevo movimiento de inventario y actualiza el stock del producto.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'tipo' => 'required|in:ingreso,salida',
            'cantidad' => 'required|integer|min:1',
            'observacion' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            Inventario::create([
                'id_producto' => $request->id_producto,
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'fecha' => now(),
                'observacion' => $request->observacion,
            ]);

            $producto = Producto::findOrFail($request->id_producto);

            if ($request->tipo === 'ingreso') {
                $producto->stock_actual += $request->cantidad;
            } elseif ($request->tipo === 'salida') {
                if ($producto->stock_actual < $request->cantidad) {
                    DB::rollBack();
                    return redirect()->back()->withErrors(['cantidad' => 'No hay suficiente stock para esta salida. Stock actual: ' . $producto->stock_actual]);
                }
                $producto->stock_actual -= $request->cantidad;
            }
            $producto->save();

            DB::commit();

            return redirect()->route('admin.inventario.index')
                             ->with('success', 'Movimiento de inventario registrado y stock actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Hubo un error al registrar el movimiento de inventario: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el movimiento de inventario especificado.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Inertia\Response
     */
    public function show(Inventario $inventario)
    {
        $inventario->load('producto');
        return Inertia::render('Admin/Inventario/Show', [
            'movimiento' => $inventario,
        ]);
    }

    /**
     * Muestra el formulario para editar el movimiento de inventario especificado y registra la visita.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Inertia\Response
     */
    public function edit(Inventario $inventario)
    {
        $inventario->load('producto');
        $productos = Producto::select('id', 'nombre', 'stock_actual')->get();

        // Lógica para el contador de visitas de la página de edición de Inventario (ID 9)
        // Nota: Este contador es para la página de edición en general, no por cada movimiento individual.
        $visitableId = 9;
        $visitableType = Inventario::class . '_EditPage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Inventario/Edit', [
            'movimiento' => $inventario,
            'productos' => $productos,
            'pageVisits' => $pageVisits, // Pasa el conteo a la vista
        ]);
    }

    /**
     * Actualiza el movimiento de inventario especificado y ajusta el stock.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Inventario $inventario)
    {
        $request->validate([
            'id_producto' => 'required|exists:productos,id',
            'tipo' => 'required|in:ingreso,salida',
            'cantidad' => 'required|integer|min:1',
            'observacion' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($inventario->id_producto);

            if ($inventario->tipo === 'ingreso') {
                $producto->stock_actual -= $inventario->cantidad;
            } elseif ($inventario->tipo === 'salida') {
                $producto->stock_actual += $inventario->cantidad;
            }
            $producto->save();

            $inventario->update([
                'id_producto' => $request->id_producto,
                'tipo' => $request->tipo,
                'cantidad' => $request->cantidad,
                'observacion' => $request->observacion,
            ]);

            $producto_nuevo = Producto::findOrFail($request->id_producto);

            if ($request->tipo === 'ingreso') {
                $producto_nuevo->stock_actual += $request->cantidad;
            } elseif ($request->tipo === 'salida') {
                if ($producto_nuevo->stock_actual < $request->cantidad) {
                    DB::rollBack();
                    return redirect()->back()->withErrors(['cantidad' => 'No hay suficiente stock para esta salida. Stock actual: ' . $producto_nuevo->stock_actual]);
                }
                $producto_nuevo->stock_actual -= $request->cantidad;
            }
            $producto_nuevo->save();

            DB::commit();

            return redirect()->route('admin.inventario.index')
                             ->with('success', 'Movimiento de inventario actualizado y stock ajustado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Hubo un error al actualizar el movimiento de inventario: ' . $e->getMessage());
        }
    }

    /**
     * Elimina el movimiento de inventario especificado y ajusta el stock.
     *
     * @param  \App\Models\Inventario  $inventario
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Inventario $inventario)
    {
        DB::beginTransaction();

        try {
            $producto = Producto::findOrFail($inventario->id_producto);

            if ($inventario->tipo === 'ingreso') {
                $producto->stock_actual -= $inventario->cantidad;
            } elseif ($inventario->tipo === 'salida') {
                $producto->stock_actual += $inventario->cantidad;
            }
            $producto->save();

            $inventario->delete();

            DB::commit();

            return redirect()->route('admin.inventario.index')
                             ->with('success', 'Movimiento de inventario eliminado y stock ajustado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Hubo un error al eliminar el movimiento de inventario: ' . $e->getMessage());
        }
    }
}

