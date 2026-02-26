<?php

namespace Grosv\LaravelPasswordlessLogin\Traits;

use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\PasswordlessLogin as PasswordlessLoginFacade;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

trait PasswordlessLogin
{
    public function getGuardNameAttribute(): string
    {
        return config('laravel-passwordless-login.user_guard');
    }

    public function getShouldRememberLoginAttribute(): bool
    {
        return config('laravel-passwordless-login.remember_login');
    }

    public function getLoginRouteExpiresInAttribute(): int
    {
        return config('laravel-passwordless-login.login_route_expires');
    }

    public function getRedirectUrlAttribute(): string
    {
        return config('laravel-passwordless-login.redirect_on_success');
    }

    public function getLoginUseOnceAttribute(): bool
    {
        return config()->boolean('laravel-passwordless-login.login_use_once');
    }

    public function createPasswordlessLoginLink(): string
    {
        return (new LoginUrl($this))->generate();
    }

    /**
     * This is a callback called on a successful login.
     */
    public function onPasswordlessLoginSuccess(Request $request): RedirectResponse|Redirector
    {
        return ($request->has('redirect_to')) ? redirect($request->redirect_to) : redirect($this->getRedirectUrlAttribute());
    }

    public function generateLoginUrl(): string
    {
        return PasswordlessLoginFacade::forUser($this)->generate();
    }
}
