<?php

namespace Tests;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesPermissionsSeeder;
use Database\Seeders\SettingsSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /** Seed roles, permissions and program settings for a test. */
    protected function seedRbac(): void
    {
        $this->seed(RolesPermissionsSeeder::class);
        $this->seed(SettingsSeeder::class);
    }

    protected function superAdmin(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::where('name', User::ROLE_SUPER_ADMIN)->value('id'),
        ], $attributes));
    }

    protected function adminUser(array $attributes = []): User
    {
        return User::factory()->create(array_merge([
            'role_id' => Role::where('name', User::ROLE_ADMIN)->value('id'),
        ], $attributes));
    }
}
