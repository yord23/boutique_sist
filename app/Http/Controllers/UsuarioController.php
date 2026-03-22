<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UsuarioController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        //
        try {
        $query = User::query();

        if ($request->has('q')) {
            $query->where("name", "LIKE", "%" . $request->q . "%");
        }

        // AGREGAMOS ->with('permissions') para que Vue reciba los permisos de cada usuario
        $usuarios = $query->with('permissions') 
                          ->orderBy('id', 'desc')
                          ->get();

        return response()->json($usuarios, 200);
    } catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
    }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            "name"     => "required|string",
            "email"    => "required|email|unique:users",
            "password" => "required|min:6",
            "role"     => "required",
            "phone"    => "nullable|string" // El teléfono es opcional
        ]);

        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->password = Hash::make($request->password);
        $usuario->role = $request->role;
        $usuario->phone = $request->phone;
        $usuario->is_active = true; // Por defecto entra activo
        $usuario->save();

        // NUEVO: Spatie - Asigna el rol en la tabla de permisos
        $usuario->assignRole($request->role);

        // NUEVO: Spatie - Si desde Vue mandamos permisos extra directos
        if ($request->has('permissions_list')) {
            $usuario->syncPermissions($request->permissions_list);
        }
        // REGISTRO EN AUDITORÍA
        ActivityLog::storeLog('CREAR_USUARIO', "Se registró al nuevo usuario: {$usuario->name} con rol {$usuario->role}");
        return response()->json(["mensaje" => "Usuario registrado"], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
        $usuario = User::select('id', 'name', 'email', 'role', 'phone', 'is_active', 'created_at')
                       ->findOrFail($id);

        return response()->json($usuario, 200);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
        $request->validate([
            "name"  => "required|string",
            "email" => "required|email|unique:users,email,$id",
            "role"  => "required",
            "phone" => "nullable|string",
            "is_active" => "required|boolean" // Vue enviará true/false
        ]);
        
        $usuario = User::findOrFail($id);
        // Guardamos el rol anterior para el log si es que cambia
        $rolAnterior = $usuario->role;
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->role = $request->role;
        $usuario->phone = $request->phone;
        //$usuario->is_active = $request->is_active; // Aquí capturamos el cambio de estado

        // Manejo del estado activo/inactivo
        if ($request->has('is_active')) {
            $usuario->is_active = $request->is_active;
        }
        if ($request->filled('password')) {
            $usuario->password = Hash::make($request->password);
        }

        $usuario->save();
        // NUEVO: Spatie - Sincroniza el rol (quita el viejo y pone el nuevo)
        $usuario->syncRoles([$request->role]);

        // NUEVO: Spatie - Sincroniza permisos manuales enviados desde Vue
        if ($request->has('permissions_list')) {
            $usuario->syncPermissions($request->permissions_list);
        }
        // REGISTRO EN AUDITORÍA
        ActivityLog::storeLog('EDITAR_USUARIO', "Se actualizaron los datos de: {$usuario->name}. Rol previo: {$rolAnterior}, Nuevo rol: {$usuario->role}");

        return response()->json(["mensaje" => "Usuario actualizado"], 200);
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $usuario = User::findOrFail($id);
        $nombreEliminado = $usuario->name;
        $usuario->delete();

        // REGISTRO EN AUDITORÍA
        ActivityLog::storeLog('ELIMINAR_USUARIO', "Se eliminó al usuario: {$nombreEliminado}");
        return response()->json(["mensaje" => "Usuario eliminado permanentemente"], 200);
    }

    /**
     * OPCIONAL: Restaurar un usuario (Por si lo borraste por error)
     */
    public function restore(string $id)
    {
        $usuario = User::withTrashed()->findOrFail($id);
        $usuario->restore(); // Limpia el campo deleted_at

        return response()->json(["mensaje" => "Usuario restaurado"], 200);
    }
}
