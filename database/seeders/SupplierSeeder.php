<?php

namespace Database\Seeders;

use App\Models\Supplier;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Supplier::create([
            'name' => 'Distribuidora Textil S.A.',
            'email' => 'ventas@textil.com',
            'phone' => '123456789',
            'tax_id' => '123456789-0',
            'address' => 'Av. Principal 123',
            'status' => true
        ]);
    }
}
