<?php

/**
 * @param $string
 *
 * @return mixed
 */


use App\Models\Permission;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;



if (!function_exists('createNewToken')) {
    /**
     * Get module name from middleware
     *
     * @return string
     */

    function createNewToken()
    {

        $key = config('app.key');

        if (Str::startsWith($key, 'base64:')) {
            $hashKey = base64_decode(substr($key, 7));
        }

        return hash_hmac('sha256', Str::random(40), $hashKey);
    }
}
    if (!function_exists('hashString')) {

        function hashString($string)
        {
            return app('hash')->make($string);
        }

}

if (!function_exists('moduleRoutes')) {
    /**
     * Get all module routes
     *
     * @return Collection
     */
    function moduleRoutes(): Collection
    {
        return collect(app('router')->getRoutes())
            ->map(function ($route) {
                return [
                    'module' => getModuleName($route['action']),
                    'permissionName' => getNamedRoute($route['action']),
                ];
            })
            ->groupBy('module');
    }
}
if (!function_exists('getModuleName')) {
    /**
     * Get module name from middleware
     *
     * @param array $action
     *
     * @return null|string
     */

    function getModuleName(array $action)
    {
        if (!isset($action['middleware'])) {
            return '';
        }
        if (is_array($action['middleware'])) {
            foreach ($action['middleware'] as $middleware) {
                $arr = explode(':', $middleware);
                if ($arr[0] === 'module') {
                    return $arr[1];
                }
            }
        }

        if (is_string($action['middleware'])) {
            $arr = explode(':', $action['middleware']);
            if ($arr[0] === 'module') {
                return $arr[1];
            }
        }
    }
}
if (!function_exists('matchPassword')) {
    /**
     * Match password
     *
     * @param string $password
     *
     * @param        $hashedPassword
     *
     * @return boolean
     */
    function matchPassword($password, $hashedPassword)
    {
        return app('hash')->check($password, $hashedPassword);
    }
}

