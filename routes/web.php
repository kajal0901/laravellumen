<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(
    [
        'prefix' => 'api',
    ],
    function ($router) {
        /**
         * Guest Routes
         */
        $router->group(
            [
                'prefix' => 'auth',
                'namespace' => 'Auth',
            ],
            function ($router) {
                $router->post(
                    '/register', [
                        'as' => 'auth.register',
                        'uses' => 'RegisterController@create',
                    ]
                );
                $router->get(
                    'email/verify', [
                        'as' => 'verification.verify',
                        'uses' => 'VerificationController@verify',
                    ]
                );
                $router->post(
                    '/login', [
                        'as' => 'auth.login',
                        'uses' => 'LoginController@login',
                    ]
                );
                $router->post(
                    '/forgot-password', [
                        'as' => 'password.email',
                        'uses' => 'ForgotPasswordController@sendResetLinkEmail',
                    ]
                );
                $router->post(
                    '/password/reset/{token}', [
                        'as' => 'password.reset',
                        'uses' => 'ResetPasswordController@postReset',
                    ]
                );
             }

        );

        /**
         * Authenticated User Routes
         */
        $router->group(
            [
               // 'middleware' => 'auth:api',
            ],
            function ($router) {

                $router->group(
                    [
                        'namespace' => 'Auth',
                    ],

                    function ($router) {
                     /* change password api */
                        $router->post(
                            '/change-password', [
                                'as' => 'change.password',
                                'uses' => 'ChangePasswordController@changePassword',
                            ]
                        );
                        /* Profile Api*/
                        $router->get(
                            '/profile', [
                                'as' => 'profile.show',
                                'uses' => 'ProfileController@show',
                            ]
                        );
                        /* Logout Api*/
                        $router->get(
                            '/logout', [
                                'as' => 'logout',
                                'uses' => 'LoginController@logout',
                            ]
                        );
                    }
                );
            }
        );
    });
