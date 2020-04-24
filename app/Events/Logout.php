<?php

namespace App\Events;

use App\Models\User;

class Logout extends Event
{
    public $user;

    /**
     * Logout constructor.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
