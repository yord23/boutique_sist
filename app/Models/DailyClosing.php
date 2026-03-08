<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyClosing extends Model
{
    //
    protected $fillable = [
    'user_id', 
    'date', 
    'opening_balance', 
    'cash_sales', 
    'card_sales', 
    'total_costs', 
    'net_profit', 
    'expected_cash', 
    'actual_cash', 
    'difference', 
    'status', 
    'notes'
];
public function user()
{
    return $this->belongsTo(User::class);
}
}
