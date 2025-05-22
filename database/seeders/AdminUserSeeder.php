<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email    = 'westpoint@admin.com';
        $name     = 'System Administrator';
        $password = 'ChangeMe123!';

        // Fetch the Admin role
        $adminRole = Role::firstWhere('name', 'Admin');

        $admin = User::updateOrCreate(
            ['email' => $email],
            [
                'name'     => $name,
                'password' => Hash::make($password),
                'role_id'  => $adminRole->id,
            ]
        );

        $admin->assignRole($adminRole);
    }
}
