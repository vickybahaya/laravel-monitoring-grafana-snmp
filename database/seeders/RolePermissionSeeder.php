<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create Roles
        $adminRole = DB::table('roles')->insertGetId([
            'name' => 'admin',
            'display_name' => 'Administrator',
            'description' => 'Full system access',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $operatorRole = DB::table('roles')->insertGetId([
            'name' => 'operator',
            'display_name' => 'Operator',
            'description' => 'Can manage routers and view monitoring',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $viewerRole = DB::table('roles')->insertGetId([
            'name' => 'viewer',
            'display_name' => 'Viewer',
            'description' => 'Read-only access to monitoring',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create Permissions
        $permissions = [
            ['name' => 'users.view', 'display_name' => 'View Users'],
            ['name' => 'users.create', 'display_name' => 'Create Users'],
            ['name' => 'users.edit', 'display_name' => 'Edit Users'],
            ['name' => 'users.delete', 'display_name' => 'Delete Users'],
            
            ['name' => 'routers.view', 'display_name' => 'View Routers'],
            ['name' => 'routers.create', 'display_name' => 'Create Routers'],
            ['name' => 'routers.edit', 'display_name' => 'Edit Routers'],
            ['name' => 'routers.delete', 'display_name' => 'Delete Routers'],
            
            ['name' => 'monitoring.view', 'display_name' => 'View Monitoring'],
            ['name' => 'monitoring.export', 'display_name' => 'Export Monitoring Data'],
            
            ['name' => 'categories.manage', 'display_name' => 'Manage Categories'],
            ['name' => 'settings.manage', 'display_name' => 'Manage Settings'],
        ];

        $permissionIds = [];
        foreach ($permissions as $permission) {
            $permissionIds[$permission['name']] = DB::table('permissions')->insertGetId([
                'name' => $permission['name'],
                'display_name' => $permission['display_name'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Assign Permissions to Roles
        // Admin gets all permissions
        foreach ($permissionIds as $permissionId) {
            DB::table('role_permission')->insert([
                'role_id' => $adminRole,
                'permission_id' => $permissionId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Operator gets router and monitoring permissions
        $operatorPermissions = [
            'routers.view', 'routers.create', 'routers.edit', 'routers.delete',
            'monitoring.view', 'monitoring.export', 'categories.manage'
        ];
        foreach ($operatorPermissions as $permName) {
            DB::table('role_permission')->insert([
                'role_id' => $operatorRole,
                'permission_id' => $permissionIds[$permName],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Viewer gets only view permissions
        $viewerPermissions = ['routers.view', 'monitoring.view'];
        foreach ($viewerPermissions as $permName) {
            DB::table('role_permission')->insert([
                'role_id' => $viewerRole,
                'permission_id' => $permissionIds[$permName],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Create default admin user
        DB::table('users')->insert([
            'name' => 'Admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role_id' => $adminRole,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create default categories
        DB::table('router_categories')->insert([
            [
                'name' => 'Core Router',
                'description' => 'Main backbone routers',
                'color' => '#ef4444',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Distribution Router',
                'description' => 'Distribution layer routers',
                'color' => '#3b82f6',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Access Router',
                'description' => 'Access layer routers',
                'color' => '#10b981',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
