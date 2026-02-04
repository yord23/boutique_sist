<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\UsuarioController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1/auth')->group(function(){

    Route::post('login', [AuthController::class, "funLogin"]);
    Route::post('register', [AuthController::class, "funRegister"]);
    
    Route::middleware('auth:sanctum')->group(function(){
    
        Route::get('profile', [AuthController::class, "funProfile"]);
        Route::post('logout', [AuthController::class, "funLogout"]);
        
    });
});
// Rutas Protegidas
    Route::middleware('auth:sanctum')->group(function () {
        // CRUD de Usuarios (Empleados)
        // Esto reemplaza escribir las 5 rutas manualmente
        Route::apiResource('usuario', UsuarioController::class);
        Route::apiResource('productos', ProductoController::class);
        Route::apiResource('categorias', CategoriaController::class);
        Route::apiResource('marcas', MarcaController::class);
        
    });