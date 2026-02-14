<?php

namespace Grosv\LaravelPasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service class to keep the controller clean.
 */
class PasswordlessLoginService
{
    /**
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;

    /**
     * @var string
     */
    private $cacheKey;

    public function __construct()
    {
        $this->user = $this->getUser();
        $this->cacheKey = request('user_type').request('expires');
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
            return Auth::guard(config('laravel-passwordless-login.user_guard'))
                ->getProvider()
                ->retrieveById(request('uid'));
        }
    }

    /**
     * Caches this request.
     *
     * @param Request $request
     *
     * @return void
     *
     * @throws \Exception
     */
    public function cacheRequest(Request $request): void
    {
        $routeExpiration = $this->usesTrait()
            ? $this->user->login_route_expires_in
            : config('laravel-passwordless-login.login_route_expires');

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
        $loginOnce = $this->usesTrait()
            ? $this->user->login_use_once
            : config('laravel-passwordless-login.login_use_once');

        return !$loginOnce || !cache()->has($this->cacheKey);
    }
}
