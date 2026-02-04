<?php

namespace App\Http\Controllers;

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

        // autenticar (Laravel busca el usuario y compara el hash automáticamente)
        if(!Auth::attempt($credenciales)){
            return response()->json(["mensaje" => "Credenciales Incorrectas"], 401);
        }

        // generar token
        $token = $request->user()->createToken("token auth")->plainTextToken;

        // responder (con el formato exacto de tu interceptor)
        return response()->json([
            "access_token" => $token,
            "user" => $request->user()
        ], 201);
    }

    public function funRegister(Request $request){
        // validar
        $request->validate([
            "name" => "required",
            "email" => "required|email|unique:users",
            "password" => "required|same:c_password"
        ]);

        // guardamos usuarios
        $usuario = new User();
        $usuario->name = $request->name;
        $usuario->email = $request->email;
        // IMPORTANTE: Encriptar antes de guardar para que funLogin funcione
        $usuario->password = Hash::make($request->password); 
        $usuario->save();

        // respondemos
        return response()->json(["mensaje" => "Usuario Registrado"], 201);
    }

    public function funProfile(Request $request){
        $usuario = $request->user();
        return response()->json($usuario, 200);
    }

    public function funLogout(Request $request){
        // Elimina todos los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json(["mensaje" => "Logout"], 200);
    }
}