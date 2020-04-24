<?php

namespace App\Repositories\Auth;


use App\Models\User;
use Exception;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class RegisterRepository
{

    /**
     * @var Model
     */
    protected $model;

    /**
     * @param array $input
     *
     * @return User
     * @throws Exception
     */
    public function process(array $input): User
    {
            $createUser = $this->create($input);
            return $createUser;
    }

    /**
     * @param array $input
     *
     * @return User
     */
    public function create(array $input): User
    {

        $user =  User::create(
            [
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => Hash::make($input['password']),
            ]
        );

        $this->assignRole(
            $user,
            config('constants.DEFAULT_USER_ROLE')
        );

        event(new Registered($user));

        return $user;
    }

    /**
     * @param User   $user
     * @param string $role
     *
     * @return User
     */
    public function assignRole(User $user, string $role): User
    {
        return $user->assignRole($role);
    }

}