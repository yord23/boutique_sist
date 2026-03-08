<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\DailyClosing;
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
        // 1. Validar que el usuario tenga una caja abierta antes de procesar la venta
    $cajaAbierta = DailyClosing::where('user_id', Auth::id())
        ->where('status', 'open')
        ->exists();

    if (!$cajaAbierta) {
        return response()->json([
            'status' => 'error',
            'message' => 'No puedes realizar ventas sin haber abierto la caja primero.'
        ], 403);
    }
        // Validación de los datos del carrito
        $request->validate([
            'customer_id' => 'nullable|exists:customers,id',
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
                    'customer_id' => $request->customer_id,
                    'user_id' => Auth::id() ?? 1, 
                    'total' => $total,
                    'tax_amount' => $total * 0.15, // Ajustar según tu impuesto local
                    'received_amount' => $request->received_amount,
                    'change_amount' => $request->received_amount - $total,
                    'payment_method' => $request->payment_method,
                    'status' => $request->payment_method === 'credit' ? 'pending' : 'completed',
                ]);

                // --- AQUÍ INSERTAS EL CÓDIGO DE CRÉDITO ---
                if ($request->payment_method === 'credit') {
                    if (!$request->customer_id) {
                        throw new \Exception("Para ventas a crédito es obligatorio seleccionar un cliente.");
                    }
                    
                    $customer = Customer::find($request->customer_id);
                    if (!$customer) {
                        throw new \Exception("Cliente no encontrado.");
                    }

                    // Aumentamos el saldo del cliente
                    $customer->increment('debt', $total);
}

                // Procesar ítems y descontar stock
                foreach ($request->items as $item) {
                    // 1. Buscamos la variante real en la DB para traer el costo real
                    $variant = ProductVariant::findOrFail($item['product_variant_id']);
                    $order->items()->create([
                        'product_variant_id' => $variant->id,
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                        'cost_price' => $variant->cost_price, // <--- Guardamos el costo histórico
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
    public function historial()
{
    // Obtenemos los cierres con el nombre del usuario que los hizo
    $historial = DailyClosing::with('user:id,name')
        ->orderBy('created_at', 'desc')
        ->get();

    return response()->json($historial);
}
}
