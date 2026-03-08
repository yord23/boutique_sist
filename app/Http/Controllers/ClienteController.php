<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class ClienteController extends Controller
{
    //
    public function index()
    {
        // Retornamos todos los clientes ordenados alfabéticamente
        return response()->json(Customer::orderBy('name', 'asc')->get());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string'
        ]);

        $cliente = Customer::create($request->all());
        return response()->json($cliente, 201);
    }
}
