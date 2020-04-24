<?php

namespace App\Providers;


use App\Events\Logout;
use App\Listeners\LogoutListener;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Laravel\Lumen\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Auth\Events\Registered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        Logout::class => [
            LogoutListener::class,
        ],

    ];
}
