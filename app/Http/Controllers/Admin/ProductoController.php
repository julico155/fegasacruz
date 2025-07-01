<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Visit; // Importa el modelo Visit

class ProductoController extends Controller
{
    /**
     * Muestra una lista de los productos y registra la visita.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $productos = Producto::with('categoria')->latest()->paginate(10);

        // Lógica para el contador de visitas de la página de índice de Productos (ID 4)
        $visitableId = 4;
        $visitableType = Producto::class . '_IndexPage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Productos/Index', [
            'products' => $productos,
            'success' => session('success'),
            'pageVisits' => $pageVisits, // Pasa el conteo a la vista
        ]);
    }

    /**
     * Muestra el formulario para crear un nuevo producto y registra la visita.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $categories = Categoria::all();

        // Lógica para el contador de visitas de la página de creación de Productos (ID 5)
        $visitableId = 5;
        $visitableType = Producto::class . '_CreatePage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Productos/Create', [
            'categories' => $categories,
            'pageVisits' => $pageVisits, // Pasa el conteo a la vista
        ]);
    }

    /**
     * Almacena un nuevo producto en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos',
            'descripcion' => 'nullable|string|max:1000',
            'precio_unitario' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'id_categoria' => 'required|exists:categorias,id',
        ]);

        Producto::create($request->all());

        return redirect()->route('admin.productos.index')
                         ->with('success', 'Producto creado exitosamente.');
    }

    /**
     * Muestra el producto especificado.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Inertia\Response
     */
    public function show(Producto $producto)
    {
        $producto->load('categoria');
        return Inertia::render('Admin/Productos/Show', [
            'product' => $producto,
        ]);
    }

    /**
     * Muestra el formulario para editar el producto especificado y registra la visita.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Inertia\Response
     */
    public function edit(Producto $producto)
    {
        $categories = Categoria::all();
        $producto->load('categoria');

        // Lógica para el contador de visitas de la página de edición de Productos (ID 6)
        // Nota: Este contador es para la página de edición en general, no por cada producto individual.
        $visitableId = 6;
        $visitableType = Producto::class . '_EditPage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Productos/Edit', [
            'product' => $producto,
            'categories' => $categories,
            'pageVisits' => $pageVisits, // Pasa el conteo a la vista
        ]);
    }

    /**
     * Actualiza el producto especificado en la base de datos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Producto $producto)
    {
        $request->validate([
            'nombre' => 'required|string|max:255|unique:productos,nombre,' . $producto->id,
            'descripcion' => 'nullable|string|max:1000',
            'precio_unitario' => 'required|numeric|min:0',
            'stock_actual' => 'required|integer|min:0',
            'id_categoria' => 'required|exists:categorias,id',
        ]);

        $producto->update($request->all());

        return redirect()->route('admin.productos.index')
                         ->with('success', 'Producto actualizado exitosamente.');
    }

    /**
     * Elimina el producto especificado de la base de datos.
     *
     * @param  \App\Models\Producto  $producto
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Producto $producto)
    {
        $producto->delete();

        return redirect()->route('admin.productos.index')
                         ->with('success', 'Producto eliminado exitosamente.');
    }
}

