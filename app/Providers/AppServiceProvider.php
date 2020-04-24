<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        Validator::extend('unique_encrypted', function ($attribute, $value, $parameters) {
            $table = $parameters[0];

            $column = $parameters[1];

            if (User::from($table)
                ->where([$column => $value])
                ->count()
            ) {
                return false;
            }
            return true;
        });
    }
}
