<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venta extends Model
{
    use HasFactory;

    protected $table = 'ventas'; // Nombre de la tabla

    protected $fillable = [
        'user_id',
        'fecha_venta',
        'total',
        'estado',
    ];

        protected $casts = [
        'fecha_venta' => 'datetime', // Para manejar la fecha como objeto DateTime
        'total' => 'float',
    ];
    

    /**
     * Obtiene el usuario (cliente) que realizó esta venta.
     */
    public function user()
    {
        // Asume que el cliente es un usuario registrado en la tabla 'users'
        return $this->belongsTo(User::class);
    }

    /**
     * Obtiene los detalles de los productos de esta venta.
     */
    public function detalles()
    {
        return $this->hasMany(DetalleVenta::class);
    }
}

