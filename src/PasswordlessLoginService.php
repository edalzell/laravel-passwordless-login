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
     *
     * @return bool
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

            $guard = (new (UserClass::fromSlug(request('user_type'))))->guard_name ?? config('laravel-passwordless-login.user_guard');
            
            return Auth::guard($guard)
                ->getProvider()
                ->retrieveById(request('uid'));
        }
    }

    /**
     * Caches this request.
     *
     * @param Request $request
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
     *
     * @return bool
     */
    public function requestIsNew(): bool
    {
        if ($this->usesTrait()) {
            $loginOnce = $this->user->login_use_once;
        } else {
            $loginOnce = config('laravel-passwordless-login.login_use_once');
        }

        if (!$loginOnce || !cache()->has($this->cacheKey)) {
            return true;
        } else {
            return false;
        }
    }
}
