<?php

namespace Database\Seeders;

use App\Models\Size;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $sizes = ['XS', 'S', 'M', 'L', 'XL', '32', '34', '36'];
        foreach ($sizes as $size) {
            Size::create(['name' => $size]);
        }
    }
}
