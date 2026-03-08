<?php

namespace App\Http\Controllers;

use App\Models\DailyClosing;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CajaController extends Controller
{
    //
    // Verifica si hay una caja abierta para el usuario actual
    public function estado(){
        $caja = DailyClosing::where('status', 'open')
            ->where('user_id', Auth::id())
            ->first();

        return response()->json([
            'abierta' => !!$caja,
            'caja' => $caja
        ]);
    }

    // Registra la apertura de la caja
    public function abrir(Request $request) {
        $request->validate([
            'monto_inicial' => 'required|numeric|min:0'
        ]);

        $userId = Auth::id();

        // Evitar abrir doble caja
        $existe = DailyClosing::where('status', 'open')
            ->where('user_id', $userId)
            ->exists();

        if ($existe) {
            return response()->json(['message' => 'Ya tienes una caja abierta'], 400);
        }

        $caja = DailyClosing::create([
            'user_id'         => $userId,
            'date'            => now()->format('Y-m-d'),
            'opening_balance' => $request->monto_inicial,
            'expected_cash'   => $request->monto_inicial, // Al abrir, lo esperado es el fondo inicial
            'status'          => 'open',
            'cash_sales'      => 0,
            'card_sales'      => 0,
            'net_profit'      => 0,
        ]);

        return response()->json([
            'message' => 'Caja abierta con éxito',
            'caja' => $caja
        ]);
    }
    // 3. Cierra la caja y calcula diferencias
public function cerrar(Request $request){
    $request->validate([
            'efectivo_real' => 'required|numeric|min:0',
            'notas' => 'nullable|string'
        ]);

        $userId = Auth::id();
        $hoy = now()->format('Y-m-d');

        $caja = DailyClosing::where('status', 'open')
            ->where('user_id', $userId)
            ->first();

        if (!$caja) {
            return response()->json(['message' => 'No hay una caja abierta para cerrar'], 404);
        }

        // 1. Sumar ventas por método de pago usando el modelo Order importado
        $efectivoVendido = Order::whereDate('created_at', $hoy)
            ->where('payment_method', 'cash')
            ->sum('total');

        $tarjetaVendido = Order::whereDate('created_at', $hoy)
            ->where('payment_method', 'card')
            ->sum('total');

        // 2. Calcular utilidad usando OrderItem importado
        $totalCostos = OrderItem::whereHas('order', function($q) use ($hoy) {
                $q->whereDate('created_at', $hoy);
            })->sum(DB::raw('quantity * cost_price'));

        $ventasTotales = $efectivoVendido + $tarjetaVendido;
        $esperado = $caja->opening_balance + $efectivoVendido;

        // 3. Actualizar el registro
        $caja->update([
            'cash_sales'    => $efectivoVendido,
            'card_sales'    => $tarjetaVendido,
            'total_costs'   => $totalCostos,
            'net_profit'    => $ventasTotales - $totalCostos,
            'expected_cash' => $esperado,
            'actual_cash'   => $request->efectivo_real,
            'difference'    => $request->efectivo_real - $esperado,
            'status'        => 'closed',
            'notes'         => $request->notas
        ]);

        return response()->json([
            'message' => 'Caja cerrada exitosamente',
            'resumen' => $caja
        ]);
    }
   public function stats(){
    try {
        $hoy = now()->startOfDay();
        $mes = now()->startOfMonth();

        // --- DATOS PARA WIDGETS ---
        $ventasHoy = Order::where('created_at', '>=', $hoy)->where('status', 'completed')->sum('total');
        $pedidosHoy = Order::where('created_at', '>=', $hoy)->where('status', 'completed')->count();
        
        // Ganancia Neta: Ventas del mes - Costo de los productos del mes
        $totalVentasMes = Order::where('created_at', '>=', $mes)->where('status', 'completed')->sum('total');
        $totalCostosMes = OrderItem::whereHas('order', function($q) use ($mes) {
            $q->where('created_at', '>=', $mes)->where('status', 'completed');
        })->sum(DB::raw('quantity * cost_price'));
        $gananciaMensual = $totalVentasMes - $totalCostosMes;
    // --- DATOS PARA GRÁFICAS ---
        // Ventas de los últimos 7 días (incluyendo hoy)
        $ventasDiarias = Order::where('created_at', '>=', now()->subDays(6))
            ->where('status', 'completed')
            ->selectRaw('DATE(created_at) as date, SUM(total) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

    // Métodos de pago
        $metodosPago = Order::where('created_at', '>=', $mes)
            ->where('status', 'completed')
            ->selectRaw('payment_method as label, COUNT(*) as value')
            ->groupBy('payment_method')
            ->get();

        return response()->json([
                'widgets' => [
                    'ventas_hoy' => (float)$ventasHoy,
                    'pedidos_hoy' => $pedidosHoy,
                    'ganancia_mes' => (float)$gananciaMensual,
                    'clientes_nuevos' => 0 // Dato de ejemplo o puedes contarlos de tu tabla customers
                ],
            'lineData' => $ventasDiarias,
            'pieData' => $metodosPago
        ]);
        }catch (\Exception $e) {
        return response()->json(['error' => $e->getMessage()], 500);
        }
    }
    // Agrega esto dentro de tu clase CajaController
    public function historial() {
        try {
            // Obtenemos los cierres de caja ordenados del más reciente al más antiguo
            $historial = DailyClosing::with('user')
                ->where('user_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json($historial);
        } catch (\Exception $e) {
            //return response()->json(['error' => 'No se pudo obtener el historial'], 500);
            // En desarrollo, es mejor ver el error real
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}

