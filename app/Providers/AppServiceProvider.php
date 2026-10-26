<?php

namespace App\Providers;

use App\Domain\Auth\Permissions;
use App\Domain\Auth\PinHasher;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // PIN hasher needs its cost config injected, so bind it explicitly.
        $this->app->singleton(PinHasher::class, function ($app) {
            return new PinHasher($app['config']->get('pin'));
        });
    }

    public function boot(): void
    {
        // Super admins implicitly pass every ability check.
        Gate::before(function ($user) {
            return $user instanceof User && $user->isSuperAdmin() ? true : null;
        });

        // One Gate ability per permission, backed by the role's permission set.
        foreach (array_keys(Permissions::all()) as $permission) {
            Gate::define($permission, function ($user) use ($permission) {
                return $user instanceof User && $user->hasPermission($permission);
            });
        }
    }
}
