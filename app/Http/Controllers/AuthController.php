<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function funLogin(Request $request){
        // validar
        $credenciales = $request->validate([
            "email" => "required|email",
            "password" => "required"
        ]);

        // autenticar
        if(!Auth::attempt($credenciales)){
            return response()->json(["mensaje" => "Credenciales Incorrectas"], 401);
        }

        $user = Auth::user();

        // 1. Verificamos si el usuario está activo (si usas la columna is_active)
        if (!$user->is_active) {
            Auth::logout();
            return response()->json(["mensaje" => "Cuenta suspendida. Contacte al administrador."], 403);
        }

        // 2. Generar token
        $token = $user->createToken("token auth")->plainTextToken;

        // 3. Extraer Roles y Permisos para Vue
        // getAllPermissions() trae los permisos del rol + los individuales
        $permisos = $user->getAllPermissions()->pluck('name');
        $rol = $user->getRoleNames()->first();

        ActivityLog::storeLog('LOGIN', "El usuario {$user->name} inició sesión.");

        // responder
        return response()->json([
            "access_token" => $token,
            "token_type" => "Bearer",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "role" => $rol, // Rol dinámico de Spatie
                "permissions" => $permisos // Lista de strings ['usuarios.crear', ...]
            ]
        ], 201);
    }

    public function funRegister(Request $request){
     $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|same:c_password"
        ]);

        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        $usuario->password = Hash::make($request->password); 
        $usuario->role = 'vendedor'; // Mantengo tu columna por compatibilidad
        $usuario->is_active = true;
        $usuario->save();

        // ASIGNACIÓN CON SPATIE
        $usuario->assignRole('vendedor'); 

        return response()->json(["mensaje" => "Usuario Registrado"], 201);
    }

    public function funProfile(Request $request){
        $user = $request->user();
        
        // Al pedir el perfil, también refrescamos roles y permisos
        return response()->json([
            "id" => $user->id,
            "name" => $user->name,
            "email" => $user->email,
            "role" => $user->getRoleNames()->first(),
            "permissions" => $user->getAllPermissions()->pluck('name')
        ], 200);
    }

    public function funLogout(Request $request){
        // Elimina todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json(["mensaje" => "Logout"], 200);
    }
}