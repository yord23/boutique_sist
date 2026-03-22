<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Models\Purchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    //
    public function index() 
    {
        // Traemos las compras con sus detalles y productos relacionados
        return Purchase::with(['items.variant.product', 'items.variant.size', 'items.variant.color'])
            ->orderBy('created_at', 'desc')
            ->get();
    }
    public function store(Request $request)
    {
        $request->validate([
            'supplier_name' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.variant_id' => 'required|exists:product_variants,id',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.cost' => 'required|numeric|min:0',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // 1. Calcular el total de la compra
                $totalAmount = collect($request->items)->sum(function($item) {
                    return $item['qty'] * $item['cost'];
                });

                // 2. Crear la cabecera de la compra
                $purchase = Purchase::create([
                    'user_id' => Auth::id(),
                    'supplier_name' => $request->supplier_name,
                    'total_amount' => $totalAmount,
                    'notes' => $request->notes
                ]);

                // 3. Procesar cada producto
                foreach ($request->items as $item) {
                    // Guardar el detalle en la tabla intermedia
                    $purchase->items()->create([
                        'product_variant_id' => $item['variant_id'],
                        'quantity' => $item['qty'],
                        'cost_price' => $item['cost']
                    ]);

                    // ACTUALIZAR EL INVENTARIO
                    $variant = ProductVariant::find($item['variant_id']);
                    
                    // Sumamos la cantidad comprada al stock actual
                    $variant->increment('stock', $item['qty']);
                    
                    // Actualizamos el precio de costo (para el cálculo de utilidades)
                    $variant->update(['cost_price' => $item['cost']]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Compra registrada y stock actualizado correctamente',
                    'purchase' => $purchase->load('items')
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error al registrar la compra: ' . $e->getMessage()
            ], 500);
        }
    }

    // Método extra para obtener los productos con stock bajo (Alertas)
    public function alerts()
    {
        $lowStock = ProductVariant::with('product')
            ->whereColumn('stock', '<=', 'min_stock')
            ->get();
            
        return response()->json($lowStock);
    }
}
