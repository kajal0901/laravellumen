<?php

namespace App\Providers;

use App\Repositories\Permission\PermissionContract;
use App\Repositories\Permission\PermissionRepository;
use App\Repositories\Role\RoleContract;
use App\Repositories\Role\RoleRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->bind(
            PermissionContract::class,
            PermissionRepository::class
        );
    }
}
