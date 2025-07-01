<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Producto extends Model
{
    use HasFactory;

    // Define el nombre de la tabla si no sigue la convención de pluralización de Laravel (products -> productos)
    protected $table = 'productos';

    // Define la clave primaria si no es 'id' (en este caso, es 'id', pero se mantiene para claridad)
    protected $primaryKey = 'id';

    // Los campos que pueden ser asignados masivamente
    protected $fillable = [
        'nombre',
        'descripcion',
        'precio_unitario',
        'stock_actual',
        'id_categoria', // Clave foránea para la categoría
    ];


        protected $casts = [
        'precio_unitario' => 'float',
        'stock_actual' => 'integer',
    ];

    
    /**
     * Obtiene la categoría a la que pertenece este producto.
     * La clave foránea en la tabla 'productos' es 'id_categoria'.
     * La clave local en la tabla 'categorias' (modelo Categoria) es 'id'.
     */
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'id_categoria', 'id');
    }

        public function detallesVenta()
    {
        return $this->hasMany(DetalleVenta::class, 'producto_id', 'id');
    }

}

