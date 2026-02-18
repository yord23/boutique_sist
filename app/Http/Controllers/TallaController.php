<?php

namespace App\Http\Controllers;

use App\Models\Size;
use Illuminate\Http\Request;

class TallaController extends Controller
{
    // Listar todas las tallas
    public function index()
    {
        try {
            $tallas = Size::all();
            return response()->json($tallas, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    // Guardar una nueva talla
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:sizes,name|max:50'
        ]);

        try {
            $talla = Size::create([
                'name' => $request->name
            ]);
            return response()->json($talla, 201);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear talla'], 500);
        }
    }

    // Mostrar una sola talla
    public function show($id)
    {
        $talla = Size::find($id);
        if (!$talla) {
            return response()->json(['message' => 'Talla no encontrada'], 404);
        }
        return response()->json($talla);
    }

    // Actualizar talla
    public function update(Request $request, $id)
    {
        $talla = Size::find($id);
        if (!$talla) return response()->json(['message' => 'No encontrado'], 404);

        $request->validate([
            'name' => 'required|string|max:50|unique:sizes,name,' . $id
        ]);

        $talla->update($request->only('name'));
        return response()->json($talla);
    }

    // Eliminar talla
    public function destroy($id)
    {
        $talla = Size::find($id);
        if (!$talla) return response()->json(['message' => 'No encontrado'], 404);

        try {
            $talla->delete();
            return response()->json(['message' => 'Talla eliminada correctamente']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'No se puede eliminar porque está en uso'], 400);
        }
    }
}