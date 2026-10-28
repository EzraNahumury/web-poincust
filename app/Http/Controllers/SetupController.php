<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;

/**
 * One-time, token-gated web installer for hosts without SSH access.
 *
 * It runs migrations + the safe seeders and creates the first super admin from
 * environment variables. It is INERT unless SETUP_TOKEN is set in .env, so once
 * you remove SETUP_TOKEN after setup the endpoint returns 404. The admin is only
 * created while no users exist yet.
 *
 * Required .env while installing:
 *   SETUP_TOKEN=<long-random-string>
 *   SETUP_ADMIN_NAME="Super Admin"
 *   SETUP_ADMIN_USERNAME=superadmin
 *   SETUP_ADMIN_EMAIL=admin@domain
 *   SETUP_ADMIN_PASSWORD=<strong-password>
 */
class SetupController extends Controller
{
    public function run(string $token): Response
    {
        $expected = (string) env('SETUP_TOKEN', '');

        // Disabled unless a token is configured, and only on an exact match.
        abort_if($expected === '' || ! hash_equals($expected, $token), 404);

        $log = [];

        Artisan::call('migrate', ['--force' => true]);
        $log[] = "== migrate ==\n" . trim(Artisan::output());

        Artisan::call('db:seed', ['--force' => true]);
        $log[] = "== seed ==\nroles, permissions & settings OK";

        $log[] = $this->maybeCreateAdmin();

        $log[] = "== SELESAI ==\nHAPUS SETUP_TOKEN (dan SETUP_ADMIN_*) dari .env sekarang agar endpoint ini nonaktif.";

        return response('<!doctype html><meta charset=utf-8><title>Setup</title>'
            . '<body style="font:14px/1.5 monospace;background:#0f1b32;color:#e3e9f3;padding:24px">'
            . '<pre>' . e(implode("\n\n", $log)) . '</pre>');
    }

    private function maybeCreateAdmin(): string
    {
        if (User::count() > 0) {
            return "== admin ==\ndilewati: sudah ada akun admin.";
        }

        $username = env('SETUP_ADMIN_USERNAME');
        $email = env('SETUP_ADMIN_EMAIL');
        $password = env('SETUP_ADMIN_PASSWORD');
        $name = env('SETUP_ADMIN_NAME', 'Super Admin');

        if (! $username || ! $email || ! $password) {
            return "== admin ==\ndilewati: SETUP_ADMIN_USERNAME/EMAIL/PASSWORD belum lengkap di .env.";
        }

        $role = Role::where('name', User::ROLE_SUPER_ADMIN)->first();

        User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password, // hashed by the model cast
            'role_id' => $role?->id,
            'status' => 'active',
        ]);

        return "== admin ==\nsuper admin '{$username}' dibuat. Segera login & ganti password.";
    }
}
