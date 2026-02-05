<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    //
    use HasFactory, SoftDeletes;
    protected $fillable = ['name', 'tax_id', 'phone', 'email', 'address', 'status'];

    public function products() {
        return $this->hasMany(Product::class);
    }
}
