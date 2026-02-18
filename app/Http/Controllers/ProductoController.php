<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request) {
        // Usamos 'images' porque así se llama en tu modelo, NO 'product_images'
    $products = Product::with(['category', 'brand', 'supplier', 'variants.size', 'variants.color', 'images'])
        ->orderBy('id', 'desc')
        ->paginate($request->limit ?? 10);
            
    return response()->json($products);
    }

    public function store(Request $request) {
        // TRUCO: Decodificar el JSON de variantes antes de validar
        if (is_string($request->variants)) {
            $request->merge([
                'variants' => json_decode($request->variants, true),
            ]);
        }
        $request->validate([
            'name' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'base_price' => 'required|numeric',
            'variants' => 'required|array|min:1'
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Crear el producto base
            $product = Product::create($request->only([
                'name', 'description', 'category_id', 'brand_id', 'supplier_id', 'base_price', 'status'
            ]));

            // 2. Crear las variantes (Talla + Color + Stock)
            foreach ($request->variants as $variant) {
                $product->variants()->create([
                    'size_id'  => $variant['size_id'],
                    'color_id' => $variant['color_id'],
                    'barcode'  => $variant['barcode']?? null,
                    'stock'    => $variant['stock']?? 0,
                    'price'    => $variant['price'] ?? $request->base_price
                ]);
            }

            // 4. Procesar la imagen física si fue enviada
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('productos', 'public');
                $product->images()->create([
                    'url' => $path, // Esto genera /storage/productos/nombre.jpg
                    'is_primary' => true
                ]);
            }

            return response()->json($product->load('variants', 'images'), 201);
        });
    }

    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);

        // Pre-procesar variantes igual que en store
        if (is_string($request->variants)) {
            $request->merge([
                'variants' => json_decode($request->variants, true),
            ]);
        }

        return DB::transaction(function () use ($request, $product) {
            $product->update($request->only([
                'name', 'description', 'category_id', 'brand_id', 'supplier_id', 'base_price', 'status'
            ]));

            // Actualización de variantes: Borramos las anteriores y creamos nuevas para evitar conflictos
            if ($request->has('variants')) {
                $product->variants()->forceDelete();
                foreach ($request->variants as $variant) {
                    $product->variants()->create([
                    'size_id'  => $variant['size_id'],
                    'color_id' => $variant['color_id'],
                    'barcode'  => $variant['barcode'] ?? null,
                    'stock'    => $variant['stock'] ?? 0,
                    'price'    => $variant['price'] ?? $request->base_price
                ]);
                }
            }

            // Procesar nueva imagen en actualización
            if ($request->hasFile('image')) {
                // Opcional: Borrar imágenes anteriores físicamente aquí
                //$product->images()->delete();
                $path = $request->file('image')->store('productos', 'public');
                $product->images()->create([
                    'url' => $path, // Esto genera /storage/productos/nombre.jpg
                    'is_primary' => true
                ]);
            }
            return response()->json(['message' => 'Producto actualizado con éxito', 'product' => $product->load('images', 'category', 'variants')],200);
        });
    }

    public function destroy($id) {
        $product = Product::findOrFail($id);
        // Borra variantes e imágenes en cascada si está configurado en DB, 
        // si no, Eloquent lo maneja aquí:
        $product->variants()->delete();
        $product->images()->delete();
        $product->delete();
        
        return response()->json(['message' => 'Producto eliminado por completo']);
    }
}