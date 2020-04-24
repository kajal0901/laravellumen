<?php

namespace App\Repositories\Auth;

use App\Models\User;
use Exception;

class ProfileRepository
{
    /**
     * @var User
     */
    protected $user;

    public function __construct()
    {
        $this->user = User::getLoggedInUser();
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getProfile(): array
    {
        return [
            'name' => $this->user->name,
            'email' => $this->user->email,
            'language' => $this->user->language,
            'email_verified' => ($this->user->email_verified_at != NULL) ? true : false,
            'roles' => $this->user->getRoleNames(),
        ];
    }
}
