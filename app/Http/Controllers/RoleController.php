<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    //
    public function index()
    {
        // Devuelve los roles (admin, vendedor, almacenista)
        return response()->json(Role::all());
    }
}
