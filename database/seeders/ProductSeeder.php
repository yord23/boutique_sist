<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ProductImage;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $product = Product::create([
            'name' => 'Jean Clásico Slim',
            'description' => 'Pantalón de mezclilla stretch',
            'category_id' => 2,
            'brand_id' => 3,
            'supplier_id' => 1,
            'base_price' => 45.00,
            'status' => true
        ]);

        // Variante 1: Negro
        ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 6,
            'color_id' => 1,
            'barcode' => '770123456789',
            'stock' => 20
        ]);
        // Esto hace exactamente lo mismo, pero es más corto
        $product->images()->create([
            'url' => 'https://via.placeholder.com/640x480.png/00aaee?text=Jean+Frontal',
            'is_primary' => true
        ]);
    }
}
