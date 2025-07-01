<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Visit; // Importa el modelo Visit

class CategoriaController extends Controller
{
    /**
     * Muestra una lista de todas las categorías y registra la visita.
     */
    public function index()
    {
        $categorias = Categoria::paginate(10);

        // Lógica para el contador de visitas de la página de índice de Categorías (ID 1)
        $visitableId = 1;
        $visitableType = Categoria::class . '_IndexPage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Categorias/Index', [
            'categorias' => $categorias,
            'success' => session('success'),
            'error' => session('error'),
            'pageVisits' => $pageVisits, // Pasa el contador a la vista
        ]);
    }

    /**
     * Muestra el formulario para crear una nueva categoría y registra la visita.
     */
    public function create()
    {
        // Lógica para el contador de visitas de la página de creación de Categorías (ID 2)
        $visitableId = 2;
        $visitableType = Categoria::class . '_CreatePage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Categorias/Create', [
            'pageVisits' => $pageVisits, // Pasa el contador a la vista
        ]);
    }

    /**
     * Almacena una nueva categoría en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre',
            'descripcion' => 'nullable|string',
        ]);

        try {
            Categoria::create($request->all());
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría creada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al crear la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Muestra el formulario para editar una categoría existente y registra la visita.
     */
    public function edit(Categoria $categoria)
    {
        // Lógica para el contador de visitas de la página de edición de Categorías (ID 3)
        // Nota: Este contador es para la página de edición en general, no por cada categoría individual.
        $visitableId = 3;
        $visitableType = Categoria::class . '_EditPage';
        $visit = Visit::firstOrCreate(['visitable_id' => $visitableId, 'visitable_type' => $visitableType], ['count' => 0]);
        $visit->increment('count');
        $pageVisits = $visit->count;

        return Inertia::render('Admin/Categorias/Edit', [
            'categoria' => $categoria,
            'pageVisits' => $pageVisits, // Pasa el contador a la vista
        ]);
    }

    /**
     * Actualiza una categoría existente en la base de datos.
     */
    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nombre' => 'required|string|max:100|unique:categorias,nombre,' . $categoria->id,
            'descripcion' => 'nullable|string',
        ]);

        try {
            $categoria->update($request->all());
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría actualizada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al actualizar la categoría: ' . $e->getMessage());
        }
    }

    /**
     * Elimina una categoría de la base de datos.
     */
    public function destroy(Categoria $categoria)
    {
        try {
            if ($categoria->productos()->count() > 0) {
                return redirect()->back()->with('error', 'No se puede eliminar la categoría porque tiene productos asociados.');
            }
            $categoria->delete();
            return redirect()->route('admin.categorias.index')->with('success', 'Categoría eliminada exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error al eliminar la categoría: ' . $e->getMessage());
        }
    }
}

