<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Grosv\LaravelPasswordlessLogin\PasswordlessLoginManager forUser(User $user)
 * @method static string generate()
 * @method static void invalidateForUser(User $user)
 */
class PasswordlessLogin extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'passwordless-login';
    }
}
