<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Size extends Model
{
    //
    protected $fillable = ['name', 'hex_code'];
    // Relación con variantes (opcional pero recomendado)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class, 'size_id');
    }
}
