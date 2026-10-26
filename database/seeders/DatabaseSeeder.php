<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Safe baseline seed: roles, permissions and program settings only.
     * No admin account with a known password is ever created here — use the
     * `php artisan admin:create` command (README §22). Demo customers/
     * transactions live in DemoSeeder and are never run automatically in prod.
     */
    public function run(): void
    {
        $this->call([
            RolesPermissionsSeeder::class,
            SettingsSeeder::class,
        ]);
    }
}
