<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\User;

class SearchController extends Controller
{
    /**
     * Realiza una búsqueda global en varios modelos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function globalSearch(Request $request)
    {
        $query = $request->input('query');
        $results = [];

        if (empty($query)) {
            return Inertia::render('Search/Results', [
                'query' => $query,
                'results' => $results,
            ]);
        }

        // Búsqueda en Productos
        $productos = Producto::where('nombre', 'like', '%' . $query . '%')
                             ->orWhere('descripcion', 'like', '%' . $query . '%')
                             ->get();
        if ($productos->isNotEmpty()) {
            $results['productos'] = $productos->map(function($producto) {
                return [
                    'id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'descripcion' => $producto->descripcion,
                    'type' => 'Producto',
                    'url' => route('admin.productos.show', $producto->id), // Asumiendo que tienes una ruta show para productos
                ];
            });
        }

        // Búsqueda en Categorías
        $categorias = Categoria::where('nombre', 'like', '%' . $query . '%')
                               ->orWhere('descripcion', 'like', '%' . $query . '%')
                               ->get();
        if ($categorias->isNotEmpty()) {
            $results['categorias'] = $categorias->map(function($categoria) {
                return [
                    'id' => $categoria->id,
                    'nombre' => $categoria->nombre,
                    'descripcion' => $categoria->descripcion,
                    'type' => 'Categoría',
                    'url' => route('admin.categorias.show', $categoria->id), // Asumiendo que tienes una ruta show para categorías
                ];
            });
        }

        // Búsqueda en Inventario (por observación y por nombre de producto relacionado)
        $inventario = Inventario::with('producto')
                                ->where(function($q) use ($query) {
                                    $q->where('observacion', 'like', '%' . $query . '%')
                                      ->orWhereHas('producto', function($q2) use ($query) {
                                          $q2->where('nombre', 'like', '%' . $query . '%');
                                      });
                                })
                                ->get();
        if ($inventario->isNotEmpty()) {
            $results['inventario'] = $inventario->map(function($item) {
                return [
                    'id' => $item->id_movimiento, // Asumiendo que 'id_movimiento' es la PK
                    'tipo' => $item->tipo,
                    'cantidad' => $item->cantidad,
                    'observacion' => $item->observacion,
                    'producto_nombre' => $item->producto ? $item->producto->nombre : 'N/A',
                    'type' => 'Movimiento de Inventario',
                    'url' => route('admin.inventario.show', $item->id_movimiento), // Asumiendo que tienes una ruta show para inventario
                ];
            });
        }

        // Búsqueda en Usuarios
        $users = User::where('name', 'like', '%' . $query . '%')
                     ->orWhere('email', 'like', '%' . $query . '%')
                     ->get();
        if ($users->isNotEmpty()) {
            $results['usuarios'] = $users->map(function($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'type' => 'Usuario',
                    'url' => route('dashboard'), // O una ruta de perfil de usuario si existe
                ];
            });
        }

        return Inertia::render('Search/Results', [
            'query' => $query,
            'results' => $results,
        ]);
    }
}

