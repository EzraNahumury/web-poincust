<?php

namespace Database\Seeders;

use App\Domain\Auth\Permissions;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class RolesPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Permissions (idempotent).
        $permissions = [];
        foreach (Permissions::all() as $name => $description) {
            $permissions[$name] = Permission::updateOrCreate(
                ['name' => $name],
                ['description' => $description],
            );
        }

        // Super admin — holds everything (also bypasses via Gate::before).
        $superAdmin = Role::updateOrCreate(
            ['name' => User::ROLE_SUPER_ADMIN],
            ['label' => 'Super Admin', 'description' => 'Akses penuh ke seluruh sistem'],
        );
        $superAdmin->permissions()->sync(collect($permissions)->pluck('id'));

        // Admin / cashier — operational subset.
        $admin = Role::updateOrCreate(
            ['name' => User::ROLE_ADMIN],
            ['label' => 'Admin / Kasir', 'description' => 'Operasional harian: customer, transaksi, ulang tahun, laporan'],
        );
        $admin->permissions()->sync(
            collect(Permissions::adminDefaults())
                ->map(fn ($name) => $permissions[$name]->id)
                ->all()
        );
    }
}
