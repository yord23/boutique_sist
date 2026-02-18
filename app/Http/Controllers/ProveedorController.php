<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProveedorController extends Controller
{
    public function index() {
        return response()->json(Supplier::all());
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'nullable|email',
            'phone' => 'nullable|string'
        ]);
        $supplier = Supplier::create($data);
        return response()->json($supplier, 201);
    }

    public function update(Request $request, $id) {
        $supplier = Supplier::findOrFail($id);
        $supplier->update($request->all());
        return response()->json($supplier);
    }

    public function destroy($id) {
        Supplier::destroy($id);
        return response()->json(['message' => 'Proveedor eliminado']);
    }
}
