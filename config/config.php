<?php

use Grosv\LaravelPasswordlessLogin\HandleAuthenticatedUsers;

return [
    'user_model' => env('LPL_USER_MODEL', 'App\User'),
    'user_guard' => env('LPL_USER_GUARD', 'web'),
    'remember_login' => env('LPL_REMEMBER_LOGIN', false),
    'login_route' => env('LPL_LOGIN_ROUTE', '/magic-login'),
    'login_route_name' => env('LPL_LOGIN_ROUTE_NAME', 'magic-login'),
    'login_route_expires' => env('LPL_LOGIN_ROUTE_EXPIRES', 30),
    'redirect_on_success' => env('LPL_REDIRECT_ON_LOGIN', '/'),
    'login_use_once' => env('LPL_USE_ONCE', false),
    'invalid_signature_message' => env('LPL_INVALID_SIGNATURE_MESSAGE', ''),
    'middleware' => env('LPL_MIDDLEWARE', ['web', HandleAuthenticatedUsers::class]),
];
