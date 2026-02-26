<?php

namespace Grosv\LaravelPasswordlessLogin\Traits;

use Grosv\LaravelPasswordlessLogin\LoginUrl;

/**
 * Logs in a user without a password.
 */
trait PasswordlessLogin
{
    /**
     * Returns the guard set for this user.
     */
    public function getGuardNameAttribute(): string
    {
        return config('laravel-passwordless-login.user_guard');
    }

    /**
     * Whether a user should be remembered on login.
     */
    public function getShouldRememberLoginAttribute(): bool
    {
        return config('laravel-passwordless-login.remember_login');
    }

    /**
     * Returns the number of minutes the route will expire in from the current time.
     */
    public function getLoginRouteExpiresInAttribute(): int
    {
        return config('laravel-passwordless-login.login_route_expires');
    }

    /**
     * Returns the url to redirect to on successful login.
     */
    public function getRedirectUrlAttribute(): string
    {
        return config('laravel-passwordless-login.redirect_on_success');
    }

    /**
     * Returns whether or not to use link once.
     *
     * @return bool
     */
    public function getLoginUseOnceAttribute()
    {
        return config('laravel-passwordless-login.login_use_once');
    }

    public function createPasswordlessLoginLink()
    {
        return (new LoginUrl($this))->generate();
    }

    /**
     * This is a callback called on a successful login.
     *
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function onPasswordlessLoginSuccess($request)
    {
        return ($request->has('redirect_to')) ? redirect($request->redirect_to) : redirect($this->getRedirectUrlAttribute());
    }

    /**
     * Generates the login link for this user.
     *
     * @return string
     */
    public function generateLoginUrl()
    {
        return \Grosv\LaravelPasswordlessLogin\PasswordlessLogin::forUser($this)->generate();
    }
}
