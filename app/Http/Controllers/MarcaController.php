<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MarcaController extends Controller
{
   public function index() {
        return response()->json(Brand::all(), 200);
    }

    public function store(Request $request) {
        $data = $request->validate(['name' => 'required|string|unique:brands,name']);
        $brand = Brand::create($data);
        return response()->json($brand, 201);
    }

    public function update(Request $request, $id) {
        $brand = Brand::findOrFail($id);
        $brand->update($request->validate(['name' => 'required|string']));
        return response()->json($brand);
    }

    public function destroy($id) {
        Brand::destroy($id);
        return response()->json(['message' => 'Marca eliminada']);
    }
}
