<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    //
    protected $fillable = ['customer_id', 'user_id', 'total', 'payment_method', 'status'];

    public function items() { return $this->hasMany(OrderItem::class); }
    public function seller() { return $this->belongsTo(User::class, 'user_id'); }
    public function customer() { return $this->belongsTo(Customer::class); }
}
