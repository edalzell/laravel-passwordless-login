<?php

namespace Grosv\LaravelPasswordlessLogin;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\URL;

class LoginUrl
{
    private readonly string $routeName;

    private readonly Carbon $routeExpires;

    private ?string $redirectUrl = null;

    public function __construct(private readonly User $user)
    {
        $this->routeExpires = now()->addMinutes($this->user->login_route_expires_in ?? config('laravel-passwordless-login.login_route_expires'));

        $this->routeName = config('laravel-passwordless-login.login_route_name');
    }

    public function setRedirectUrl(string $redirectUrl): void
    {
        $this->redirectUrl = $redirectUrl;
    }

    public function generate(): string
    {
        $url = URL::temporarySignedRoute(
            $this->routeName,
            $this->routeExpires,
            [
                'uid' => $this->user->getAuthIdentifier(),
                'redirect_to' => $this->redirectUrl,
                'user_type' => UserClass::toSlug(get_class($this->user)),
            ]
        );

        cache()->put(UserClass::cacheKey($this->user), true, $this->routeExpires);

        return $url;
    }
}
