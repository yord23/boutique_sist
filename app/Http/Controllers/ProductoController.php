<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductoController extends Controller
{
    /**
     * Listado con BUSCADOR AVANZADO y PAGINACIÓN
     */
    public function index(Request $request)
    {
        $limit = $request->limit ?? 10;
        $q = $request->q;
        $category_id = $request->category_id;
        $brand_id = $request->brand_id;

        // Iniciamos la consulta uniendo tablas para mostrar nombres en lugar de IDs
        $query = DB::table('products as p')
            ->join('categories as c', 'p.category_id', '=', 'c.id')
            ->join('brands as b', 'p.brand_id', '=', 'b.id')
            ->select(
                'p.*', 
                'c.name as categoria_nombre', 
                'b.name as marca_nombre'
            )
            ->whereNull('p.deleted_at') // Por si usas SoftDeletes manual
            ->orderBy('p.id', 'desc');

        // Buscador por nombre de producto
        if ($q) {
            $query->where('p.name', 'like', "%$q%");
        }

        // Filtros específicos (Categoría o Marca)
        if ($category_id) {
            $query->where('p.category_id', $category_id);
        }
        if ($brand_id) {
            $query->where('p.brand_id', $brand_id);
        }

        $productos = $query->paginate($limit);

        return response()->json($productos, 200);
    }

    /**
     * Guardar Producto + Variantes + Imágenes (Uso de Transacciones)
     */
    public function store(Request $request)
    {
        $request->validate([
            "name"         => "required|max:255",
            "category_id"  => "required|exists:categories,id",
            "brand_id"     => "required|exists:brands,id",
            "supplier_id"  => "required|exists:suppliers,id",
            "base_price"   => "required|numeric|min:0",
            "variants"     => "required|array|min:1", // Al menos una talla/color
        ]);

        return DB::transaction(function () use ($request) {
            // 1. Insertar Producto Base
            $productId = DB::table("products")->insertGetId([
                "name"         => $request->name,
                "description"  => $request->description,
                "base_price"   => $request->base_price,
                "category_id"  => $request->category_id,
                "brand_id"     => $request->brand_id,
                "supplier_id"  => $request->supplier_id,
                "status"       => true,
                "created_at"   => now(),
                "updated_at"   => now()
            ]);

            // 2. Insertar Variantes (Tallas, Colores y Stock)
            foreach ($request->variants as $variant) {
                DB::table("product_variants")->insert([
                    "product_id" => $productId,
                    "size_id"    => $variant['size_id'],
                    "color_id"   => $variant['color_id'],
                    "barcode"    => $variant['barcode'],
                    "stock"      => $variant['stock'],
                    "price"      => $variant['price'] ?? $request->base_price,
                    "created_at" => now(),
                    "updated_at" => now()
                ]);
            }

            // 3. Insertar Imágenes (si existen)
            if ($request->has('images')) {
                foreach ($request->images as $img) {
                    DB::table("product_images")->insert([
                        "product_id" => $productId,
                        "url"        => $img['url'],
                        "is_primary" => $img['is_primary'] ?? false,
                        "created_at" => now(),
                        "updated_at" => now()
                    ]);
                }
            }

            return response()->json(["mensaje" => "Producto completo registrado con éxito", "id" => $productId], 201);
        });
    }

    /**
     * Mostrar detalle completo (Producto + sus variantes)
     */
    public function show(string $id)
    {
        $producto = DB::table("products")->where('id', $id)->first();

        if (!$producto) {
            return response()->json(["mensaje" => "Producto no encontrado"], 404);
        }

        // Buscamos sus variantes uniendo con nombres de talla y color
        $variantes = DB::table("product_variants as pv")
            ->join('sizes as s', 'pv.size_id', '=', 's.id')
            ->join('colors as co', 'pv.color_id', '=', 'co.id')
            ->select('pv.*', 's.name as talla', 'co.name as color')
            ->where('pv.product_id', $id)
            ->get();

        $imagenes = DB::table("product_images")->where('product_id', $id)->get();

        return response()->json([
            "producto"  => $producto,
            "variantes" => $variantes,
            "imagenes"  => $imagenes
        ], 200);
    }

    /**
     * Actualizar Producto y Variantes
     */
    public function update(Request $request, string $id)
    {
        return DB::transaction(function () use ($request, $id) {
            // Actualizar datos base
            DB::table("products")->where('id', $id)->update([
                "name"        => $request->name,
                "description" => $request->description,
                "base_price"  => $request->base_price,
                "status"      => $request->status,
                "updated_at"  => now()
            ]);

            // Sincronizar variantes: Lo más limpio es borrar y re-insertar
            if ($request->has('variants')) {
                DB::table("product_variants")->where('product_id', $id)->delete();
                foreach ($request->variants as $variant) {
                    DB::table("product_variants")->insert([
                        "product_id" => $id,
                        "size_id"    => $variant['size_id'],
                        "color_id"   => $variant['color_id'],
                        "barcode"    => $variant['barcode'],
                        "stock"      => $variant['stock'],
                        "price"      => $variant['price'] ?? $request->base_price,
                        "created_at" => now(),
                        "updated_at" => now()
                    ]);
                }
            }

            return response()->json(["mensaje" => "Producto actualizado correctamente"]);
        });
    }

    /**
     * Eliminar producto
     */
    public function destroy(string $id)
    {
        // Al borrar el producto, las variantes se borran solas si configuraste
        // ON DELETE CASCADE en la migración. Si no, bórralas manualmente aquí.
        DB::table("products")->where('id', $id)->delete();
        return response()->json(["mensaje" => "Producto eliminado"]);
    }
}