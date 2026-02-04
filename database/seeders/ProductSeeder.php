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
            'category_id' => 1,
            'brand_id' => 2,
            'supplier_id' => 1,
            'name' => 'Blusa Elegante Seda',
            'description' => 'Blusa de seda ideal para eventos formales',
            'base_price' => 45.00
        ]);

        ProductVariant::create([
            'product_id' => $product->id,
            'size_id' => 2,  // S
            'color_id' => 3, // Rojo
            'sku' => 'BLU-SED-S-ROJ',
            'stock' => 10,
            'price' => 45.00
        ]);

        ProductImage::create([
            'product_id' => $product->id,
            'color_id' => 3,
            'file_path' => 'products/sample-blusa.jpg',
            'is_primary' => true,
            'position' => 1
        ]);
    }
}
