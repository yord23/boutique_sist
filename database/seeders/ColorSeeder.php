<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $colors = ['Negro', 'Blanco', 'Rojo', 'Azul Marino', 'Beige'];
        foreach ($colors as $color) {
            Color::create(['name' => $color]);
        }
    }
}
