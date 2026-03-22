<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        // 1. Limpiar caché de Spatie
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Crear todos los roles posibles (Asegúrate de incluir 'almacenista')
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $vendedor = Role::firstOrCreate(['name' => 'vendedor']);
        $almacenista = Role::firstOrCreate(['name' => 'almacenista']); // <--- Faltaba este

        // 3. Crear Permisos básicos
        $permisos = [
            'usuarios.crear', 
            'usuarios.eliminar', 
            'ventas.realizar',
            'productos.gestionar'
        ];

        foreach ($permisos as $p) {
            Permission::firstOrCreate(['name' => $p]);
        }

        // 4. Asignar todo al admin
        $admin->syncPermissions(Permission::all());

        // 5. Migrar usuarios con "Seguro de Vida"
        $usuarios = User::all();
        foreach ($usuarios as $u) {
            if ($u->role) {
                // Verificamos si el rol existe en Spatie antes de asignarlo
                $existeRol = Role::where('name', $u->role)->exists();
                
                if ($existeRol) {
                    $u->syncRoles([$u->role]);
                } else {
                    // Si el rol no existe (ej. un error de dedo), le ponemos vendedor por defecto
                    $u->syncRoles(['vendedor']);
                    $this->command->warn("Usuario {$u->name} tenía un rol desconocido ({$u->role}), se asignó 'vendedor'.");
                }
            }
        }
        
        $this->command->info("¡Roles y permisos migrados con éxito!");
    }
}
