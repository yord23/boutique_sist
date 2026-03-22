<?php

use App\Http\Controllers\AbonoController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CajaController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ColorController;
use App\Http\Controllers\MarcaController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RolePermissionController;
use App\Http\Controllers\TallaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\VentaController;

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
        //Route::get('marcas/listado', [MarcaController::class, 'getList']);
        Route::apiResource('marcas', MarcaController::class);

        // --- MÓDULO DE PROVEEDORES ---
        // Ruta para llenar selects (sin paginación)
        //Route::get('proveedores/listado', [ProveedorController::class, 'getList']);
        Route::apiResource('proveedores', ProveedorController::class);

        // --- MÓDULO DE TALLAS (SIZES) ---
        Route::apiResource('tallas', TallaController::class);

        // --- MÓDULO DE COLORES ---
        Route::apiResource('colores', ColorController::class);
        Route::post('/ventas', [VentaController::class, 'store']);
        // Rutas para el control de caja
    Route::prefix('caja')->group(function () {
        Route::get('/estado', [CajaController::class, 'estado']);
        Route::post('/abrir', [CajaController::class, 'abrir']);
        Route::post('/cerrar', [CajaController::class, 'cerrar']);
        Route::get('/historial', [CajaController::class, 'historial']);
        Route::get('stats', [CajaController::class, 'stats']);
        Route::get('/clientes', [ClienteController::class, 'index']);
        Route::post('/abonos', [AbonoController::class, 'store']);
        Route::get('/abonos/cliente/{id}', [AbonoController::class, 'porCliente']);
            // Rutas de Compras e Inventario
        Route::post('purchases', [PurchaseController::class, 'store']);
        Route::get('inventory/alerts', [PurchaseController::class, 'alerts']);
        // En routes/api.php
        Route::get('products/search', [ProductoController::class, 'search']);
        Route::get('purchases', [PurchaseController::class, 'index']);

        Route::get('inventory/report', [ProductoController::class, 'stockActual']);
        Route::get('products/pricing', [ProductoController::class, 'getPricingData']);
        Route::put('products/{id}/price', [ProductoController::class, 'updatePrice']);
    });
    // routes/api.php
Route::middleware(['auth:sanctum'])->group(function () {
    // Cambiamos audit-logs por auditoria
    Route::get('/v1/auditoria', [ActivityLogController::class, 'index']);

    Route::get('roles', [RoleController::class, 'index']);
    Route::get('permisos', [PermissionController::class, 'index']);
    Route::post('usuarios/{id}/asignar-permisos', [UsuarioController::class, 'asignarPermisos']);
    Route::apiResource('roles-gestion', RolePermissionController::class);
Route::delete('roles/{id}/permisos', [RolePermissionController::class, 'syncPermisos']);
});
    });