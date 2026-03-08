<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    //
    protected $fillable = ['name', 'phone', 'email', 'debt'];

    // Relación: Un cliente puede tener muchos abonos registrados
    public function abonos()
    {
        return $this->hasMany(Abono::class);
    }
    // Relación: Un cliente puede tener muchas órdenes
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
