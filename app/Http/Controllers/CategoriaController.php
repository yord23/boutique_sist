<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    //
    public function index() {
        return response()->json(Category::all());
    }

    public function store(Request $request) {
        $category = Category::create($request->validate(['name' => 'required|string']));
        return response()->json($category, 201);
    }
     public function show($id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json(['message' => 'Categoria no encontrada'], 404);
        }

        return response()->json($category, 200);
    }

    public function update(Request $request, $id) {
        $category = Category::findOrFail($id);
        $category->update($request->all());
        return response()->json($category);
    }

    public function destroy($id) {
    $category = Category::find($id);
    if (!$category) return response()->json(['message' => 'No encontrada'], 404);
    
    $category->delete();
    return response()->json(['message' => 'Categoría eliminada con éxito'], 200);
}
}
