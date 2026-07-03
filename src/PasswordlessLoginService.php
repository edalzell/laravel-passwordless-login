<?php

namespace Grosv\LaravelPasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

/**
 * Service class to keep the controller clean.
 */
class PasswordlessLoginService
{
    private string $cacheKey;

    public ?Authenticatable $user;

    public function __construct()
    {
        $this->user = $this->getUser();
        $this->cacheKey = request('user_type').request('uid');
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

    public function consumeRequest(): void
    {
        $loginOnce = $this->usesTrait()
            ? $this->user->login_use_once
            : config('laravel-passwordless-login.login_use_once');

        if ($loginOnce) {
            cache()->forget($this->cacheKey);
        }
    }

    public function requestIsNew(): bool
    {
        return cache()->has($this->cacheKey);
    }
}
