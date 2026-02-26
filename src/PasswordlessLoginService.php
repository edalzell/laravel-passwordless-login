<?php

namespace Grosv\LaravelPasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service class to keep the controller clean.
 */
class PasswordlessLoginService
{
    /**
     * @var User
     */
    public $user;

    /**
     * @var string
     */
    private $cacheKey;

    public function __construct()
    {
        $this->user = $this->getUser();
        $this->cacheKey = \request('user_type').\request('expires');
    }

    /**
     * Checks if this use class uses the PasswordlessLogable trait.
     */
    public function usesTrait(): bool
    {
        $traits = class_uses($this->user, true);

        return in_array(PasswordlessLogin::class, $traits);
    }

    /**
     * Get the user from the request.
     *
     * @return mixed
     */
    public function getUser()
    {
        if (request()->has('user_type')) {
            return Auth::guard(config('laravel-passwordless-login.user_guard'))
                ->getProvider()
                ->retrieveById(request('uid'));
        }
    }

    /**
     * Caches this request.
     *
     *
     * @throws \Exception
     */
    public function cacheRequest(Request $request)
    {
        if ($this->usesTrait()) {
            $routeExpiration = $this->user->login_route_expires_in;
        } else {
            $routeExpiration = config('laravel-passwordless-login.login_route_expires');
        }

        cache()->remember($this->cacheKey, $routeExpiration * 60, function () use ($request) {
            return $request->url();
        });
    }

    /**
     * Checks if this request has been made yet.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function requestIsNew(): bool
    {
        if ($this->usesTrait()) {
            $loginOnce = $this->user->login_use_once;
        } else {
            $loginOnce = config('laravel-passwordless-login.login_use_once');
        }

        if (! $loginOnce || ! cache()->has($this->cacheKey)) {
            return true;
        } else {
            return false;
        }
    }
}
