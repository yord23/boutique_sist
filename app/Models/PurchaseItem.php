<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseItem extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'product_variant_id',
        'quantity',
        'cost_price'
    ];

    // Relación: Este detalle pertenece a una compra
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    // Relación: Este detalle pertenece a una variante de producto
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'product_variant_id');
    }
}
