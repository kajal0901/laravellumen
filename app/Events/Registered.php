<?php

namespace App\Events;

use Illuminate\Contracts\Auth\Authenticatable;

class Registered extends Event
{

    /**
     * @var Authenticatable
     */
    public $user;

    /**
     * Create a new event instance.
     *
     * @param Authenticatable $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }
}
