<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductoController extends Controller
{
    public function index()
    {
        // Traemos el producto con su categoría y marca (nombres en inglés según tu migración)
        $products = Product::with(['category', 'brand'])->get();
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'brand_id'    => 'required|exists:brands,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'name'        => 'required|string|max:255',
            'base_price'  => 'required|numeric|min:0',
        ]);

        // Guardamos usando los campos exactos: name, description, base_price
        $product = Product::create($request->all());

        return response()->json([
            'message' => 'Producto creado con éxito',
            'data' => $product
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'No encontrado'], 404);

        $product->update($request->all());
        return response()->json(['message' => 'Actualizado', 'data' => $product]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'No encontrado'], 404);

        $product->delete(); // Usa el SoftDeletes que vimos en tu modelo
        return response()->json(['message' => 'Producto eliminado']);
    }
}