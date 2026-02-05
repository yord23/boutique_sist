<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    //
    public function index() {
        $categorias = DB::table('categories')
            ->select('id', 'name', 'status', 'created_at')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($categorias, 200);
    }

    public function store(Request $request) {
        $request->validate([
            "name" => "required|unique:categories,name|max:100"
        ]);

        $id = DB::table("categories")->insertGetId([
            "name"       => $request->name,
            "status"     => $request->status ?? true,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        $nuevaCategoria = DB::table("categories")->where('id', $id)->first();

        return response()->json([
            "mensaje" => "Categoría creada con éxito",
            "datos"   => $nuevaCategoria
        ], 201);
    }
     public function show(string $id)
    {
        $categoria = DB::table("categories")->where('id', $id)->first();

        if (!$categoria) {
            return response()->json(["mensaje" => "Categoría no encontrada"], 404);
        }

        return response()->json($categoria, 200);
    }

    public function update(Request $request, $id) {
        // Validamos que el nombre sea único, pero ignorando el nombre de la categoría actual
        $request->validate([
            "name"   => "required|max:100|unique:categories,name," . $id,
            "status" => "boolean"
        ]);

        $existe = DB::table("categories")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "No se puede actualizar, categoría no encontrada"], 404);
        }

        DB::table("categories")
            ->where('id', $id)
            ->update([
                "name"       => $request->name,
                "status"     => $request->status,
                "updated_at" => now(),
            ]);

        return response()->json(["mensaje" => "Categoría actualizada correctamente"], 200);
    }

    public function destroy($id) {
        $existe = DB::table("categories")->where('id', $id)->exists();

            if (!$existe) {
                return response()->json(["mensaje" => "Categoría no encontrada"], 404);
            }

            // Si usas SoftDeletes manual con Query Builder:
            // DB::table("categories")->where('id', $id)->update(['deleted_at' => now()]);
            
            // Eliminación física:
            DB::table("categories")->where('id', $id)->delete();

            return response()->json(["mensaje" => "Categoría eliminada permanentemente"], 200);
    }
}
