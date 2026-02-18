<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ColorController extends Controller
{
    public function index()
    {
        return response()->json(DB::table('colors')->get(), 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:50',
            'hex_code' => 'nullable|max:7' // Ejemplo: #FFFFFF
        ]);

        $id = DB::table('colors')->insertGetId([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['mensaje' => 'Color creado', 'id' => $id], 201);
    }

    public function update(Request $request, $id)
    {
        DB::table('colors')->where('id', $id)->update([
            'name' => $request->name,
            'hex_code' => $request->hex_code,
            'updated_at' => now()
        ]);
        return response()->json(['mensaje' => 'Color actualizado']);
    }

    public function destroy($id)
    {
        DB::table('colors')->where('id', $id)->delete();
        return response()->json(['mensaje' => 'Color eliminado']);
    }

}
