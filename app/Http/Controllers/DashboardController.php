<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Producto;
use App\Models\Categoria;
use App\Models\Inventario;
use App\Models\User;
use App\Models\Venta;
use App\Models\Visit;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard con estadísticas y datos para gráficos.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Inertia\Response
     */
    public function index(Request $request)
    {
        // Estadísticas Generales
        $totalProducts = Producto::count();
        $totalCategories = Categoria::count();
        $totalInventoryMovements = Inventario::count();
        $totalUsers = User::count();
        $totalSales = Venta::count();

        // Contadores de Visitas para páginas de administración específicas
        $visits = [
            'categoriasIndex' => Visit::where('visitable_id', 1)->where('visitable_type', Categoria::class . '_IndexPage')->value('count') ?? 0,
            'categoriasCreate' => Visit::where('visitable_id', 2)->where('visitable_type', Categoria::class . '_CreatePage')->value('count') ?? 0,
            'categoriasEdit' => Visit::where('visitable_id', 3)->where('visitable_type', Categoria::class . '_EditPage')->value('count') ?? 0,
            'productosIndex' => Visit::where('visitable_id', 4)->where('visitable_type', Producto::class . '_IndexPage')->value('count') ?? 0,
            'productosCreate' => Visit::where('visitable_id', 5)->where('visitable_type', Producto::class . '_CreatePage')->value('count') ?? 0,
            'productosEdit' => Visit::where('visitable_id', 6)->where('visitable_type', Producto::class . '_EditPage')->value('count') ?? 0,
            'inventarioIndex' => Visit::where('visitable_id', 7)->where('visitable_type', Inventario::class . '_IndexPage')->value('count') ?? 0,
            'inventarioCreate' => Visit::where('visitable_id', 8)->where('visitable_type', Inventario::class . '_CreatePage')->value('count') ?? 0,
            'inventarioEdit' => Visit::where('visitable_id', 9)->where('visitable_type', Inventario::class . '_EditPage')->value('count') ?? 0,
        ];

        // Datos para el gráfico de torta: Productos por Categoría
        $productsByCategory = Producto::select('id_categoria')
            ->selectRaw('count(*) as total')
            ->with('categoria') // Carga la relación de categoría
            ->groupBy('id_categoria')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->categoria ? $item->categoria->nombre : 'Sin Categoría',
                    'value' => $item->total,
                ];
            });


        return Inertia::render('Dashboard', [
            'totalProducts' => $totalProducts,
            'totalCategories' => $totalCategories,
            'totalInventoryMovements' => $totalInventoryMovements,
            'totalUsers' => $totalUsers,
            'totalSales' => $totalSales,
            'visits' => $visits,
            'productsByCategory' => $productsByCategory, // Pasa los datos del gráfico
        ]);
    }
}
