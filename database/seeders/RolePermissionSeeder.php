<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use App\Models\Permission;  // our extended model

class RolePermissionSeeder extends Seeder
{
    public function run()
    {
        $roles = [
            'Admin',
            'Police',
            'Enforcement Officer',
            'Business Owner',
            'Business Staff',
            'Customer',
            'Account',
            'Support',
            'Auditor',
        ];

        // 1) Create roles
        foreach ($roles as $roleName) {
            Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web']
            );
        }

        // 2) Define grouped permissions
        $grouped = [
            'Police Management' => [
                'add police officer',
                'edit police officer',
                'suspend police officer',
                'view police officers',
            ],
            'Business & Staff Management' => [
                'add business owner',
                'edit business owner',
                'delete business owner',
                'add business',
                'edit business',
                'delete business',
                'add business branch',
                'edit business branch',
                'delete business branch',
                'add business staff',
                'edit business staff',
                'delete business staff',
                'assign staff to branch',
            ],
            'Receipt Management' => [
                'create receipt',
                'print receipt',
                'view receipts',
                'transfer product between receipts',
            ],
            'Product & Inventory' => [
                'create product',
                'edit product',
                'create product category',
                'edit product category',
                'report product',
                'view reported products',
                'resolve product report',
            ],
            'Wallet & Transactions' => [
                'fund wallet',
                'view transactions',
                'generate transaction report',
            ],
            'User Account' => [
                'suspend user',
                'reset user password',
                'verify user identity',
                'view user profile',
            ],
            'Auditing & Logs' => [
                'view audit logs',
                'view receipt logs',
                'view product logs',
                'view login history',
            ],
            'Enforcement Actions' => [
                'flag illegal business',
                'blacklist business',
                'issue enforcement notice',
                'view enforcement records',
            ],
            'System Actions' => [
                'edit configuration',
                'edit app setting',
            ],
        ];

        // 3) Create permissions (with guard_name, slug & category)
        foreach ($grouped as $category => $names) {
            foreach ($names as $name) {
                Permission::firstOrCreate(
                    ['name' => $name, 'guard_name' => 'web'],
                    [
                        'slug'     => Str::slug($name),
                        'category' => $category,
                    ]
                );
            }
        }

        // 4) Assign ALL permissions to Admin
        $admin = Role::findByName('Admin', 'web');
        $admin->syncPermissions(Permission::all());

        // 5) (Re-assign defaults unchanged from before…)
        Role::findByName('Police')->givePermissionTo([
            'view police officers',
            'create receipt',

            'view receipts',
            'print receipt',
            'report product',
        ]);
        Role::findByName('Enforcement Officer')->givePermissionTo([
            'view police officers',
            'flag illegal business',
            'blacklist business',
            'issue enforcement notice',
            'view enforcement records',
        ]);
        Role::findByName('Business Owner')->givePermissionTo([
            'add business branch',
            'edit business branch',
            'delete business branch',
            'add business staff',
            'edit business staff',
            'delete business staff',
            'assign staff to branch',
            'create product',
            'edit product',
            'create product category',
            'view receipts',
        ]);
        Role::findByName('Business Staff')->givePermissionTo([
            'create receipt',

            'print receipt',
            'create product',
            'edit product',
        ]);
        Role::findByName('Customer')->givePermissionTo([
            'view receipts',
        ]);
        Role::findByName('Account')->givePermissionTo([
            'fund wallet',

            'view transactions',

            'generate transaction report',
        ]);
        Role::findByName('Auditor')->givePermissionTo([
            'view audit logs',
            'view receipt logs',
            'view product logs',
            'view transactions',
            'view login history',
        ]);
    }
}
