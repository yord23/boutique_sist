<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    //
    public function index()
    {
        // Traemos los logs con los datos del usuario que los generó
        // Ordenados del más reciente al más antiguo
        return ActivityLog::with('user:id,name')->orderBy('created_at', 'desc')->get();
    }
}
