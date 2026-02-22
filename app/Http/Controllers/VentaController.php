<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentaController extends Controller
{
    //
    public function store(Request $request)
    {
        // Validación de los datos del carrito
        $request->validate([
            'payment_method' => 'required|string',
            'received_amount' => 'required|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_variant_id' => 'required|exists:product_variants,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
        ]);

        try {
            return DB::transaction(function () use ($request) {
                // Calcular el total de la venta
                $total = collect($request->items)->sum(function($item) {
                    return $item['price'] * $item['quantity'];
                });

                // Crear la cabecera (Order)
                $order = Order::create([
                    'document_type' => 'ticket',
                    'order_number' => 'VEN-' . now()->format('Ymd') . '-' . strtoupper(uniqid()),
                    'customer_id' => $request->customer_id ?? null,
                    'user_id' => Auth::id() ?? 1, 
                    'total' => $total,
                    'tax_amount' => $total * 0.15, // Ajustar según tu impuesto local
                    'received_amount' => $request->received_amount,
                    'change_amount' => $request->received_amount - $total,
                    'payment_method' => $request->payment_method,
                    'status' => 'completed'
                ]);

                // Procesar ítems y descontar stock
                foreach ($request->items as $item) {
                    $order->items()->create([
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);

                    // Descontar Stock con bloqueo
                    $variant = ProductVariant::where('id', $item['product_variant_id'])
                        ->lockForUpdate()
                        ->first();

                    if ($variant->stock < $item['quantity']) {
                        throw new \Exception("Stock insuficiente para: {$variant->barcode}");
                    }

                    $variant->decrement('stock', $item['quantity']);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Venta procesada con éxito',
                    'order_id' => $order->id
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
