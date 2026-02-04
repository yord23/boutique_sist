<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    //
    use SoftDeletes;
    protected $fillable = ['category_id', 'brand_id', 'supplier_id', 'name', 'description', 'base_price'];

    // Relación: Un producto tiene muchas variantes (tallas/colores)
    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    // Relación: Un producto tiene muchas imágenes
    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function category() { return $this->belongsTo(Category::class); }
    public function brand() { return $this->belongsTo(Brand::class); }
}
