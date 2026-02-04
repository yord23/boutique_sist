<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductVariant extends Model
{
    //
    use SoftDeletes;
    protected $fillable = ['product_id', 'size_id', 'color_id', 'sku', 'stock', 'price'];

    public function product() { return $this->belongsTo(Product::class); }
    public function size() { return $this->belongsTo(Size::class); }
    public function color() { return $this->belongsTo(Color::class); }
}
