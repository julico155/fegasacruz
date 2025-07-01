<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'visitable_id',
        'visitable_type',
        'count',
    ];

    /**
     * Define la relación polimórfica inversa.
     */
    public function visitable()
    {
        return $this->morphTo();
    }
}

