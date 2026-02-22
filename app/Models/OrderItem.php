<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    //
    use HasFactory;
    protected $fillable = ['order_id', 'product_variant_id', 'quantity', 'price'];

    // Relación: Cada ítem pertenece a una orden
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    // Relación: Cada ítem apunta a una variante de producto específica
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
