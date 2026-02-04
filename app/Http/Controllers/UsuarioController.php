<?php

namespace App\Http\Controllers;

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
        $usuarios = User::select('id', 'name', 'email', 'role', 'phone', 'is_active')
            ->where("name", "LIKE", "%$request->q%")
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($usuarios, 200);
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

        return response()->json(["mensaje" => "Usuario actualizado"], 200);
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
        $usuario = User::findOrFail($id);
        $usuario->delete();

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
