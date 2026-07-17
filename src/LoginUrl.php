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

        // Keyed by expiry rather than a single boolean so a second link for the same
        // user can't clobber an earlier, still-valid link's marker (see issue #140).
        $key = UserClass::cacheKey($this->user);
        $activeLinks = UserClass::activeLinks($key);

        // Drop already-expired entries so this array doesn't grow unbounded for users
        // who generate many links.
        $activeLinks = array_filter(
            $activeLinks,
            fn (bool $active, int $expires): bool => $expires >= now()->timestamp, ARRAY_FILTER_USE_BOTH
        );

        $activeLinks[$this->routeExpires->timestamp] = true;

        // TTL must cover the furthest-out link, not just this one, or a later
        // short-lived link would prematurely evict an existing long-lived one.
        UserClass::store()->put(
            $key,
            $activeLinks,
            Carbon::createFromTimestamp(max(array_keys($activeLinks)))
        );

        return $url;
    }
}
