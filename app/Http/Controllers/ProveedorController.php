<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    //
    /**
     * Listado con BUSCADOR y PAGINACIÓN (Lógica para tablas grandes)
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $q = $request->q;

        $query = DB::table('suppliers')
            ->select('id', 'name', 'tax_id', 'email', 'phone', 'address', 'status', 'created_at')
            ->orderBy('id', 'desc');

        // Buscador por nombre o por Identificación Tributaria (tax_id)
        if ($q) {
            $query->where(function($subQuery) use ($q) {
                $subQuery->where("name", "like", "%$q%")
                         ->orWhere("tax_id", "like", "%$q%");
            });
        }

        $proveedores = $query->paginate($limit);

        return response()->json($proveedores, 200);
    }

    /**
     * Listado SIMPLE (Para Selectores en el formulario de Productos)
     */
    public function getList()
    {
        $proveedores = DB::table('suppliers')
            ->select('id', 'name')
            ->where('status', true)
            ->orderBy('name', 'asc')
            ->get();

        return response()->json($proveedores, 200);
    }

    /**
     * Guardar un nuevo proveedor
     */
    public function store(Request $request)
    {
        $request->validate([
            "name"   => "required|max:255",
            "tax_id" => "required|unique:suppliers,tax_id|max:20",
            "email"  => "nullable|email",
            "phone"  => "nullable"
        ]);

        $id = DB::table("suppliers")->insertGetId([
            "name"       => $request->name,
            "tax_id"     => $request->tax_id,
            "email"      => $request->email,
            "phone"      => $request->phone,
            "address"    => $request->address,
            "status"     => $request->status ?? true,
            "created_at" => now(),
            "updated_at" => now()
        ]);

        return response()->json(["mensaje" => "Proveedor creado con éxito", "id" => $id], 201);
    }

    /**
     * Mostrar detalle de un proveedor
     */
    public function show(string $id)
    {
        $proveedor = DB::table("suppliers")->where('id', $id)->first();

        if (!$proveedor) {
            return response()->json(["mensaje" => "Proveedor no encontrado"], 404);
        }

        return response()->json($proveedor, 200);
    }

    /**
     * Actualizar proveedor
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            "name"   => "required",
            "tax_id" => "required|unique:suppliers,tax_id," . $id,
            "email"  => "nullable|email"
        ]);

        $existe = DB::table("suppliers")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "Proveedor no encontrado"], 404);
        }

        DB::table("suppliers")
            ->where('id', $id)
            ->update([
                "name"       => $request->name,
                "tax_id"     => $request->tax_id,
                "email"      => $request->email,
                "phone"      => $request->phone,
                "address"    => $request->address,
                "status"     => $request->status,
                "updated_at" => now()
            ]);

        return response()->json(["mensaje" => "Proveedor actualizado correctamente"]);
    }

    /**
     * Eliminar proveedor
     */
    public function destroy(string $id)
    {
        $existe = DB::table("suppliers")->where('id', $id)->exists();

        if (!$existe) {
            return response()->json(["mensaje" => "Proveedor no encontrado"], 404);
        }

        DB::table("suppliers")->where('id', $id)->delete();

        return response()->json(["mensaje" => "Proveedor eliminado"]);
    }
}
