<?php

namespace Grosv\LaravelPasswordlessLogin\Traits;

use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\PasswordlessLogin as PasswordlessLoginFacade;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;

trait PasswordlessLogin
{
    protected function guardName(): Attribute
    {
        return Attribute::make(get: fn (): string => config('laravel-passwordless-login.user_guard'));
    }

    protected function shouldRememberLogin(): Attribute
    {
        return Attribute::make(get: fn (): bool => config('laravel-passwordless-login.remember_login'));
    }

    protected function loginRouteExpiresIn(): Attribute
    {
        return Attribute::make(get: fn (): int => config('laravel-passwordless-login.login_route_expires'));
    }

    protected function redirectUrl(): Attribute
    {
        return Attribute::make(get: fn (): string => config('laravel-passwordless-login.redirect_on_success'));
    }

    protected function loginUseOnce(): Attribute
    {
        return Attribute::make(get: fn (): bool => config()->boolean('laravel-passwordless-login.login_use_once'));
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
        return ($request->has('redirect_to')) ? redirect($request->redirect_to) : redirect($this->redirect_url);
    }

    public function generateLoginUrl(): string
    {
        return PasswordlessLoginFacade::forUser($this)->generate();
    }
}
