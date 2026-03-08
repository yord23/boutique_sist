<?php

namespace App\Http\Controllers;

use App\Models\Abono;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AbonoController extends Controller
{
    //
    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required'
        ]);

        try {
            DB::beginTransaction();

            // 1. Registrar el abono
            $abono = Abono::create([
                'customer_id' => $request->customer_id,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'notes' => $request->notes,
                'date' => now(),
            ]);

            // 2. Descontar la deuda del cliente
            $cliente = Customer::find($request->customer_id);
            $cliente->debt -= $request->amount; // Restamos el abono de su deuda actual
            $cliente->save();

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Abono registrado correctamente',
                'new_debt' => $cliente->debt,
                'abono' => $abono->load('customer') // Cargamos la relación para el ticket
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
    public function porCliente($clienteId){
        $abonos = Abono::where('customer_id', $clienteId)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json($abonos);
    }
}
