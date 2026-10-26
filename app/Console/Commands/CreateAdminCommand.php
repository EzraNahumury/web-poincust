<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

/**
 * Creates an admin account securely. Prompts for a password (hidden input) so
 * no default/known credential is ever baked into a seeder (README §22).
 *
 *   php artisan admin:create
 *   php artisan admin:create --name="..." --username=... --email=... --role=super_admin --password=...
 */
class CreateAdminCommand extends Command
{
    protected $signature = 'admin:create
        {--name= : Full name}
        {--username= : Login username}
        {--email= : Email address}
        {--role=super_admin : Role name (super_admin|admin)}
        {--password= : Password (omit to be prompted securely)}';

    protected $description = 'Create an admin/cashier account';

    public function handle(): int
    {
        if (Role::count() === 0) {
            $this->error('Roles belum ada. Jalankan dulu: php artisan migrate --seed');

            return self::FAILURE;
        }

        $name = $this->option('name') ?: $this->ask('Nama lengkap');
        $username = $this->option('username') ?: $this->ask('Username');
        $email = $this->option('email') ?: $this->ask('Email');
        $roleName = $this->option('role') ?: 'super_admin';

        $role = Role::where('name', $roleName)->first();
        if (! $role) {
            $this->error("Role '{$roleName}' tidak ditemukan.");

            return self::FAILURE;
        }

        $password = $this->option('password');
        if (! $password) {
            $password = $this->secret('Password (min. 10 karakter)');
            $confirm = $this->secret('Konfirmasi password');
            if ($password !== $confirm) {
                $this->error('Konfirmasi password tidak cocok.');

                return self::FAILURE;
            }
        }

        $validator = Validator::make(compact('name', 'username', 'email', 'password'), [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:10'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $name,
            'username' => $username,
            'email' => $email,
            'password' => $password, // hashed by the model cast
            'role_id' => $role->id,
            'status' => 'active',
        ]);

        $this->info("Admin '{$user->username}' ({$role->name}) berhasil dibuat. ID: {$user->id}");

        return self::SUCCESS;
    }
}
