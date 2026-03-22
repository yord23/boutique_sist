<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class ActivityLog extends Model
{
    //
    protected $fillable = ['user_id', 'action', 'description', 'ip_address'];

    public static function storeLog($action, $description)
    {
        return self::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'description' => $description,
            'ip_address' => request()->ip(),
        ]);
    }
    public function user() {
        return $this->belongsTo(User::class, 'user_id');
    }
}
