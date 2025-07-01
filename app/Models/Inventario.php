<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventario extends Model
{
    use HasFactory;

    protected $table = 'inventario'; // Nombre de la tabla
    protected $primaryKey = 'id_movimiento'; // Clave primaria personalizada

    protected $fillable = [
        'id_producto',
        'tipo',
        'cantidad',
        'fecha',
        'observacion',
    ];

    /**
     * Obtiene el producto asociado a este movimiento de inventario.
     */
    public function producto()
    {
        // Un movimiento de inventario pertenece a un producto.
        // 'id_producto' es la clave foránea en la tabla 'inventario'.
        // 'id' es la clave primaria en la tabla 'productos' (modelo Producto).
        return $this->belongsTo(Producto::class, 'id_producto', 'id');
    }
}

