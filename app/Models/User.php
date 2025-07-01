<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    // La tabla 'users' es el nombre por defecto para el modelo 'User',
    // así que no es necesario especificar 'protected $table = 'users';'
    protected $primaryKey = 'id'; // La clave primaria es 'id' (por defecto)

    /**
     * The attributes that are mass assignable.
     *
     * Estas son las columnas que se pueden asignar masivamente (ej. al usar User::create()).
     * Incluye las nuevas columnas 'nombre', 'telefono', 'direccion' y 'tipo'.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'telefono',
        'email',
        'direccion',
        'tipo', // Asegúrate de incluir 'tipo' aquí
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * Estas columnas no se mostrarán cuando el modelo sea convertido a un array o JSON.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * Define cómo deben ser casteadas ciertas columnas (ej. a fechas, a hash).
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones (si las necesitas más adelante para Ventas, etc.)
    // Si tienes una tabla 'ventas' que referencia al usuario como 'id_cliente'
    public function ventasCliente()
    {
        return $this->hasMany(Venta::class, 'id_cliente');
    }

    // Si tienes una tabla 'ventas' que referencia al usuario como 'id_usuario_admin'
    public function ventasAdmin()
    {
        return $this->hasMany(Venta::class, 'id_usuario_admin');
    }

    // app/Models/User.php
    public function ventas()
    {
        return $this->hasMany(Venta::class);
    }
}

?>
