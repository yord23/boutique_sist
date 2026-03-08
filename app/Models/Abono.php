<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Abono extends Model
{
    use HasFactory;

    // Campos que permitimos llenar masivamente
    protected $fillable = [
        'customer_id',
        'amount',
        'payment_method',
        'notes',
        'date'
    ];

    // Relación: Un abono pertenece a un cliente
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}