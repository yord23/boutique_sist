<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;

class MarcaController extends Controller
{
    //
    /**
     * Muestra la lista de marcas.
     */
    public function index()
    {
        return response()->json(Brand::all(), 200);
    }

    /**
     * Almacena una nueva marca.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name',
            'description' => 'nullable|string'
        ]);

        $brand = Brand::create($validated);

        return response()->json([
            'message' => 'Marca creada con éxito',
            'data' => $brand
        ], 201);
    }

    /**
     * Muestra una marca específica.
     */
    public function show($id)
    {
        $brand = Brand::find($id);
        
        if (!$brand) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        }

        return response()->json($brand, 200);
    }

    /**
     * Actualiza una marca existente.
     */
    public function update(Request $request, $id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        }

        $brand->update($request->all());

        return response()->json([
            'message' => 'Marca actualizada',
            'data' => $brand
        ], 200);
    }

    /**
     * Elimina una marca (o la envía a papelera si usas SoftDeletes).
     */
    public function destroy($id)
    {
        $brand = Brand::find($id);

        if (!$brand) {
            return response()->json(['message' => 'Marca no encontrada'], 404);
        }

        $brand->delete();

        return response()->json(['message' => 'Marca eliminada con éxito'], 200);
    }
}
