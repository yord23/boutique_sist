<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TallaController extends Controller
{
    //
    /**
     * Listado SIMPLE (Lógica para tablas pequeñas)
     * Ideal para cargar tallas en el formulario de productos de Vue.
     */
    public function index()
    {
        $tallas = DB::table('sizes')
            ->select('id', 'name', 'status')
            ->orderBy('id', 'asc') // Ordenadas por ID para mantener consistencia de tamaño
            ->get();

        return response()->json($tallas, 200);
    }

    /**
     * Guardar una nueva talla
     */
    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:sizes,name|max:10" // Ej: "XL", "42", "M"
        ]);

        $id = DB::table("sizes")->insertGetId([
            "name"       => $request->name,
            "status"     => $request->status ?? true,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        return response()->json([
            "mensaje" => "Talla registrada", 
            "id" => $id
        ], 201);
    }

    /**
     * Mostrar una talla específica
     */
    public function show(string $id)
    {
        $talla = DB::table("sizes")->where('id', $id)->first();

        if (!$talla) {
            return response()->json(["mensaje" => "Talla no encontrada"], 404);
        }

        return response()->json($talla, 200);
    }

    /**
     * Actualizar talla
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "name"   => "required|max:10|unique:sizes,name," . $id,
            "status" => "boolean"
        ]);

        $existe = DB::table("sizes")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "Talla no encontrada"], 404);
        }

        DB::table("sizes")
            ->where('id', $id)
            ->update([
                "name"       => $request->name,
                "status"     => $request->status,
                "updated_at" => now(),
            ]);

        return response()->json(["mensaje" => "Talla actualizada correctamente"]);
    }

    /**
     * Eliminar talla
     */
    public function destroy(string $id)
    {
        $existe = DB::table("sizes")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "Talla no encontrada"], 404);
        }

        // Nota: Si una talla ya está amarrada a un producto, 
        // la base de datos dará error por la llave foránea (lo cual es bueno).
        DB::table("sizes")->where('id', $id)->delete();

        return response()->json(["mensaje" => "Talla eliminada"]);
    }
}
