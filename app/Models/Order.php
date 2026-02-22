<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    use HasFactory;

    protected $fillable = [
        'document_type',
        'order_number',
        'customer_id',
        'user_id',
        'total',
        'tax_amount',
        'received_amount',
        'change_amount',
        'payment_method',
        'status'];

   // Relación: Una orden tiene muchos ítems (productos detalle)
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    // Relación: Una orden pertenece a un cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    // Relación: Una orden fue realizada por un usuario (vendedor)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
