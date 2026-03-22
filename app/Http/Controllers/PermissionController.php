<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    //
    public function index()
    {
        // Devuelve todos los permisos (usuarios.crear, ventas.realizar, etc.)
        return response()->json(Permission::all());
    }
}
