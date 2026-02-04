<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    //
    protected $fillable = ['product_id', 'color_id', 'file_path', 'is_primary', 'position'];

    public function product() { return $this->belongsTo(Product::class); }
}
