<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColorController extends Controller
{
    //
    /**
     * Listado SIMPLE (Lógica para tablas pequeñas)
     */
    public function index()
    {
        $colores = DB::table('colors')
            ->select('id', 'name', 'status')
            ->orderBy('name', 'asc') // Orden alfabético para que el usuario encuentre rápido el color
            ->get();

        return response()->json($colores, 200);
    }

    /**
     * Guardar un nuevo color
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:colors,name|max:50" // Ejemplo: "Azul Marino", "Rojo Carmesí"
        ]);

        $id = DB::table("colors")->insertGetId([
            "name"       => $request->name,
            "status"     => $request->status ?? true,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        $nuevoColor = DB::table("colors")->where('id', $id)->first();

        return response()->json([
            "mensaje" => "Color registrado con éxito",
            "datos"   => $nuevoColor
        ], 201);
    }

    /**
     * Mostrar un color específico
     */
    public function show(string $id)
    {
        $color = DB::table("colors")->where('id', $id)->first();

        if (!$color) {
            return response()->json(["mensaje" => "Color no encontrado"], 404);
        }

        return response()->json($color, 200);
    }

    /**
     * Actualizar color
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "name"   => "required|max:50|unique:colors,name," . $id,
            "status" => "boolean"
        ]);

        $existe = DB::table("colors")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "Color no encontrado"], 404);
        }

        DB::table("colors")
            ->where('id', $id)
            ->update([
                "name"       => $request->name,
                "status"     => $request->status,
                "updated_at" => now(),
            ]);

        return response()->json(["mensaje" => "Color actualizado correctamente"], 200);
    }

    /**
     * Eliminar color
     */
    public function destroy(string $id)
    {
        $existe = DB::table("colors")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "Color no encontrado"], 404);
        }

        // Si el color ya se está usando en variantes, SQL impedirá el borrado (Integridad Referencial)
        DB::table("colors")->where('id', $id)->delete();

        return response()->json(["mensaje" => "Color eliminado"], 200);
    }
}
