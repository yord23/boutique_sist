<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

// Importamos los modelos aquí
use App\Models\Size;
use App\Models\Color;
use App\Models\Category;
use App\Models\Brand;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Tallas
        $sizes = ['XS', 'S', 'M', 'L', 'XL', '38', '40', '42'];
        foreach ($sizes as $size) {
            Size::create(['name' => $size]);
        }

        // Colores
        $colors = [
            ['name' => 'Negro', 'hex_code' => '#000000'],
            ['name' => 'Blanco', 'hex_code' => '#FFFFFF'],
            ['name' => 'Rojo', 'hex_code' => '#FF0000'],
            ['name' => 'Azul Marino', 'hex_code' => '#000080'],
        ];
        foreach ($colors as $color) {
            Color::create($color);
        }

        // Categorías
        $categories = ['Damas', 'Caballeros', 'Niños', 'Accesorios'];
        foreach ($categories as $cat) {
            Category::create(['name' => $cat]);
        }

        // Marcas
        $brands = ['Nike', 'Zara', 'Levi\'s', 'Generico'];
        foreach ($brands as $brand) {
            Brand::create(['name' => $brand]);
        }
    }
}
