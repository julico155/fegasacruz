<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categoria extends Model
{
    use HasFactory;

    protected $table = 'categorias';
    protected $primaryKey = 'id'; // Ya por defecto, pero se mantiene para claridad
    public $incrementing = true; // Por defecto es true
    protected $fillable = ['nombre', 'descripcion'];

    // Relación: Una categoría tiene muchos productos
    public function productos()
    {
        return $this->hasMany(Producto::class, 'id_categoria');
    }
}

?>