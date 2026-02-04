<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Support\Facades\Hash;

class UserAndSupplierSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Admin Boutique',
            'email' => 'admin@boutique.com',
            'password' => Hash::make('password'), // Forma más limpia y segura
            'role' => 'admin',
            'is_active' => true
        ]);

        Supplier::create([
            'name' => 'Distribuidora Textil S.A.',
            'email' => 'ventas@textil.com',
            'phone' => '123456789',
            'tax_id' => '123456789-0', // <--- Agrega esta línea
        ]);
    }
}
