<?php

namespace Grosv\LaravelPasswordlessLogin;

use Exception;
use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Service class to keep the controller clean.
 */
class PasswordlessLoginService
{
    private string $cacheKey;

    public Authenticatable $user;

    public function __construct()
    {
        $this->user = $this->getUser();
        $this->cacheKey = request('user_type').request('expires');
    }

    /**
     * Checks if this use class uses the PasswordlessLogable trait.
     */
    public function usesTrait(): bool
    {
        $traits = class_uses($this->user, true);

        return in_array(PasswordlessLogin::class, $traits);
    }

    public function getUser(): ?Authenticatable
    {
        if (! request()->has('user_type')) {
            return null;
        }

        return Auth::guard(config('laravel-passwordless-login.user_guard'))
            ->getProvider()
            ->retrieveById(request('uid'));
    }

    /**
     * @throws Exception
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
     * @throws InvalidArgumentException
     */
    public function requestIsNew(): bool
    {
        $loginOnce = $this->usesTrait()
            ? $this->user->login_use_once
            : config('laravel-passwordless-login.login_use_once');

        return ! $loginOnce || ! cache()->has($this->cacheKey);
    }
}
