<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleVenta extends Model
{
    use HasFactory;

    protected $table = 'detalle_ventas'; // Nombre de la tabla

    protected $fillable = [
        'venta_id',
        'producto_id',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];


        protected $casts = [
        'cantidad' => 'integer',
        'precio_unitario' => 'float',
        'subtotal' => 'float',
    ];
    
    /**
     * Obtiene la venta a la que pertenece este detalle.
     */
    public function venta()
    {
        return $this->belongsTo(Venta::class);
    }

    /**
     * Obtiene el producto asociado a este detalle de venta.
     */
    public function producto()
    {
        // 'producto_id' es la clave foránea en 'detalle_ventas'
        // 'id' es la clave primaria en 'productos' (modelo Producto)
        return $this->belongsTo(Producto::class, 'producto_id', 'id');
    }
}

