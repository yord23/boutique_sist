<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductoController extends Controller
{
    public function index(Request $request) {
        // 1. Iniciamos la consulta con sus relaciones
        $query = Product::with(['category', 'brand', 'supplier', 'variants.size', 'variants.color', 'images'])
            ->orderBy('id', 'desc');

        // 2. Lógica de búsqueda global para Lazy Loading (Filtro)
        if ($request->has('globalFilter') && $request->globalFilter != 'null' && $request->globalFilter != '') {
            $search = $request->globalFilter;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  // NUEVO: Buscar por código de barras en las variantes
                    ->orWhereHas('variants', function($v) use ($search) {
                        $v->where('barcode', $search); // Búsqueda exacta por código de barras
            });
            });
        }

        // 3. Paginación: PrimeVue envía 'rows', si no llega usamos 5 por defecto
        $perPage = $request->input('rows', 5);
        $products = $query->paginate($perPage);
            
        // 4. Devolvemos la estructura que DataTable Lazy necesita
        return response()->json([
            'data' => $products->items(),
            'total' => $products->total(),
            'per_page' => $products->perPage(),
            'current_page' => $products->currentPage()
        ]);
    }

    public function store(Request $request) {
        if (is_string($request->variants)) {
            $request->merge([
                'variants' => json_decode($request->variants, true),
            ]);
        }

        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'category_id' => 'required|exists:categories,id',
            'brand_id' => 'required|exists:brands,id',
            'supplier_id' => 'required|exists:suppliers,id',
            'base_price' => 'required|numeric|min:0',
            // Validar que el barcode sea único en la tabla de variantes
            'variants' => 'required|array',
            'variants.*.barcode' => 'required|string|unique:product_variants,barcode',
        ], [
            'name.required' => 'El nombre del producto es obligatorio.',
            'variants.*.barcode.unique' => 'El código de barras debe ser único para cada variante.'
        ]);

        return DB::transaction(function () use ($request) {
            $product = Product::create($request->only([
                'name', 'description', 'category_id', 'brand_id', 'supplier_id', 'base_price', 'status'
            ]));

            foreach ($request->variants as $variant) {
                $product->variants()->create([
                    'size_id'  => $variant['size_id'],
                    'color_id' => $variant['color_id'],
                    'barcode'  => $variant['barcode'] ?? null,
                    'stock'    => $variant['stock'] ?? 0,
                    'price'    => $variant['price'] ?? $request->base_price
                ]);
            }

            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('productos', 'public');
                $product->images()->create([
                    'url' => $path,
                    'is_primary' => true
                ]);
            }

            return response()->json($product->load('variants', 'images'), 201);
        });
    }

    public function update(Request $request, $id) {
        $product = Product::findOrFail($id);

        if (is_string($request->variants)) {
            $request->merge([
                'variants' => json_decode($request->variants, true),
            ]);
        }

        return DB::transaction(function () use ($request, $product) {
            $product->update($request->only([
                'name', 'description', 'category_id', 'brand_id', 'supplier_id', 'base_price', 'status'
            ]));

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

            if ($request->hasFile('image')) {
                // ELIMINACIÓN FÍSICA: Borramos el archivo anterior del disco para no llenar el server
                foreach ($product->images as $oldImage) {
                    Storage::disk('public')->delete($oldImage->url);
                }
                $product->images()->delete(); 

                $path = $request->file('image')->store('productos', 'public');
                $product->images()->create([
                    'url' => $path,
                    'is_primary' => true
                ]);
            }

            return response()->json([
                'message' => 'Producto actualizado con éxito', 
                'product' => $product->load('images', 'category', 'variants')
            ], 200);
        });
    }

    public function destroy($id) {
        $product = Product::findOrFail($id);
        
        // Eliminamos archivos físicos antes de borrar el registro
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->url);
        }

        $product->variants()->delete();
        $product->images()->delete();
        $product->delete();
        
        return response()->json(['message' => 'Producto eliminado por completo']);
    }
}