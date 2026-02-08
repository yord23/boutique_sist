<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\TallaController;
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
            // --- MÓDULO DE PRODUCTOS (El núcleo del sistema) ---
        // Incluye buscador avanzado y gestión de variantes/imágenes
        Route::apiResource('productos', ProductoController::class);

        // --- MÓDULO DE CATEGORÍAS ---
        Route::apiResource('categorias', CategoriaController::class);

        // --- MÓDULO DE MARCAS ---
        // Ruta para llenar selects en el frontend (sin paginación)
        Route::get('marcas/listado', [MarcaController::class, 'getList']);
        Route::apiResource('marcas', MarcaController::class);

        // --- MÓDULO DE PROVEEDORES ---
        // Ruta para llenar selects (sin paginación)
        Route::get('proveedores/listado', [ProveedorController::class, 'getList']);
        Route::apiResource('proveedores', ProveedorController::class);

        // --- MÓDULO DE TALLAS (SIZES) ---
        Route::apiResource('tallas', TallaController::class);

        // --- MÓDULO DE COLORES ---
        Route::apiResource('colores', ColorController::class);
    });