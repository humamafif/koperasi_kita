<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        // Buat role
        Role::create(['name' => 'super_admin']);
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'anggota']);
        Role::create(['name' => 'anggota_tetap']);

        // Buat permission
        Permission::create(['name' => 'upgrade_member']);
        Permission::create(['name' => 'approve_loan']);
        Permission::create(['name' => 'manage_interest']);
        Permission::create(['name' => 'create_loan']);
        Permission::create(['name' => 'create_saving']);

        // Assign permission ke role
        $superAdmin = Role::findByName('super_admin');
        $superAdmin->givePermissionTo(['upgrade_member', 'approve_loan', 'manage_interest', 'create_loan', 'create_saving']);

        $admin = Role::findByName('admin');
        $admin->givePermissionTo(['approve_loan', 'manage_interest']);

        $anggotaTetap = Role::findByName('anggota_tetap');
        $anggotaTetap->givePermissionTo(['create_loan', 'create_saving']);

        $anggota = Role::findByName('anggota');
        $anggota->givePermissionTo(['create_loan']);
    }
}
