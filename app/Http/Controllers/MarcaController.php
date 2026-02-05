<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
    //
   /**
     * Listado con BUSCADOR y PAGINACIÓN (Para tablas grandes)
     */
    public function index(Request $request)
    {
        // 1. Capturamos los parámetros de la URL: /api/marca?page=1&limit=10&q=zara
        $limit = $request->limit ?? 10;
        $q = $request->q;

        // 2. Iniciamos la consulta base
        $query = DB::table('brands')
            ->select('id', 'name', 'status', 'created_at')
            ->orderBy('id', 'desc');

        // 3. Si el usuario escribió algo en el buscador (q), filtramos
        if ($q) {
            $query->where("name", "like", "%$q%");
        }

        // 4. Ejecutamos la paginación (esto devuelve data, links, meta, etc.)
        $marcas = $query->paginate($limit);

        return response()->json($marcas, 200);
    }

    /**
     * Listado SIMPLE (Para Selectors/Combobox en Vue)
     * Usamos la segunda lógica aquí porque para un <select> no quieres paginación
     */
    public function getList()
    {
        $marcas = DB::table('brands')
            ->select('id', 'name')
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($marcas, 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            "name" => "required|unique:brands,name|max:100"
        ]);

        $id = DB::table("brands")->insertGetId([
            "name"       => $request->name,
            "status"     => $request->status ?? true,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        return response()->json(["mensaje" => "Marca creada", "id" => $id], 201);
    }

    public function show(string $id)
    {
        $marca = DB::table("brands")->where('id', $id)->first();

        if (!$marca) {
            return response()->json(["mensaje" => "Marca no encontrada"], 404);
        }

        return response()->json($marca, 200);
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            "name" => "required|max:100|unique:brands,name," . $id
        ]);

        $actualizado = DB::table("brands")
            ->where('id', $id)
            ->update([
                "name"       => $request->name,
                "status"     => $request->status,
                "updated_at" => now()
            ]);

        return response()->json(["mensaje" => "Marca actualizada"]);
    }

    public function destroy(string $id)
    {
        DB::table("brands")->where('id', $id)->delete();
        return response()->json(["mensaje" => "Marca eliminada"]);
    }
}
