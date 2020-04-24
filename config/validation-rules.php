<?php

return [
    'register' => [
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique_encrypted:users,email',
        'password' => 'required|string|min:8',
    ],
    'login' => [
        'email' => 'required|string|email|max:255',
        'password' => 'required|string|min:8',
    ],
    'forgot-password' => [
        'email' => 'required|email',
    ],
    'password-reset' => [
        'token' => 'required',
        'password' => 'required|min:8',
    ],
    'change-password' => [
        'old_password' => 'required',
        'new_password' => 'required|string|min:8',
    ],
    'profile-update' => [
        'name' => 'sometimes|required|string|max:255',
        'language' => 'sometimes|string|in:en,de',
        'deb_auto_id' => 'sometimes|digits_between:1,5',

    ]
];