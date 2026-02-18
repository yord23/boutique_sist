<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{
    public function index() {
        return response()->json(Category::all(), 200);
    }

    public function store(Request $request) {
        $data = $request->validate([
            'name' => 'required|string|unique:categories,name|max:255',
            'status' => 'required|boolean'
        ]);
        $category = Category::create($data);
        return response()->json($category, 201);
    }

    public function show($id) {
        return response()->json(Category::findOrFail($id));
    }

    public function update(Request $request, $id) {
        $category = Category::findOrFail($id);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
            'status' => 'required|boolean'
        ]);
        $category->update($data);
        return response()->json($category);
    }

    public function destroy($id) {
        $category = Category::findOrFail($id);
        $category->delete();
        return response()->json(['message' => 'Eliminado correctamente']);
    }
}
