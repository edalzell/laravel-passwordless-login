<?php

namespace Grosv\LaravelPasswordlessLogin;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\URL;

class LoginUrl
{
    private readonly string $route_name;

    private readonly Carbon $route_expires;

    private ?string $redirect_url = null;

    public function __construct(private readonly User $user)
    {
        $this->route_expires = now()->addMinutes($this->user->login_route_expires_in ?? config('laravel-passwordless-login.login_route_expires'));

        $this->route_name = config('laravel-passwordless-login.login_route_name');
    }

    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirect_url = $redirectUrl;
    }

    public function generate(): string
    {
        $url = URL::temporarySignedRoute(
            $this->route_name,
            $this->route_expires,
            [
                'uid' => $this->user->getAuthIdentifier(),
                'redirect_to' => $this->redirect_url,
                'user_type' => UserClass::toSlug(get_class($this->user)),
            ]
        );

        cache()->put(UserClass::cacheKey($this->user), true, $this->route_expires);

        return $url;
    }
}
