<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionController extends Controller
{
    //
    public function index() {
        // Traemos los roles con sus permisos cargados
        return response()->json(Role::with('permissions')->get());
    }

    public function store(Request $request) {
        $request->validate([
            'name' => 'required|unique:roles,name',
            'permissions' => 'array' // Validamos que sea un array de strings
            ]);
        
        // Creamos el rol en Spatie
        $role = Role::create(['name' => $request->name ]);
        
        // Si enviamos permisos al crear
        if ($request->has('permissions')) {
            $role->syncPermissions($request->permissions);
        }

        return response()->json(['mensaje' => 'Rol creado con éxito', 'role' => $role]);
    }

    public function destroy($id) {
        $role = Role::findOrFail($id);
        if ($role->name === 'admin') {
            return response()->json(['mensaje' => 'No puedes eliminar el rol de Administrador'], 403);
        }
        $role->delete();
        return response()->json(['mensaje' => 'Rol eliminado']);
    }
}
