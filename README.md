# Laravel Passwordless Login

[![build status](https://github.com/grosv/laravel-passwordless-login/actions/workflows/test.yml/badge.svg)](https://github.com/grosv/laravel-passwordless-login/actions/workflows/test.yml)

### A simple, safe magic login link generator for Laravel

This package provides a temporary signed route that logs in a user. What it does not provide is a way of actually sending the link to the route to the user. This is because I don't want to make any assumptions about how you communicate with your users.

### Installation
```shell script
composer require grosv/laravel-passwordless-login
```

### Simple Usage
```php
use App\User;
use Grosv\LaravelPasswordlessLogin\LoginUrl;

function sendLoginLink()
{
    $user = User::find(1);

    $generator = new LoginUrl($user);
    $generator->setRedirectUrl('/somewhere/else'); // Override the default url to redirect to after login
    $url = $generator->generate();

    //OR Use a Facade
    $url = PasswordlessLogin::forUser($user)->generate();

    // Send $url in an email or text message to your user
}
```
### Using A Trait

Because some sites have more than one user-type model (users, admins, etc.), you can use a trait to set up the default configurations for each user type. The methods below are provided by the trait, so you only need to include the ones for which you want to use a different value.

```php
use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use PasswordlessLogin;

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
}
```
If you are using the PasswordlessLogin Trait, you can generate a link using the defaults defined in the trait by simply calling `createPasswordlessLoginLink()` on the user you want to log in.

The biggest mistake I could see someone making with this package is creating a login link for one user and sending it to another. Please be careful and test your code. I don't want anyone getting mad at me for someone else's silliness.

### Multiple Guards

If you have more than one user-type model authenticated on different guards (e.g. `User` on `web` and `Admin` on `admin`), override `getGuardNameAttribute()` on each model to return its own guard instead of the globally configured one:

```php
class Admin extends Authenticatable
{
    use PasswordlessLogin;

    public function getGuardNameAttribute(): string
    {
        return 'admin';
    }
}
```

The package uses this to both retrieve and log in the user with the correct guard, so a link generated for an `Admin` is authenticated against the `admin` guard rather than `LPL_USER_GUARD`.

### Configuration
You can publish the config file or just set the values you want to use in your .env file:
```dotenv
LPL_REMEMBER_LOGIN=false
LPL_LOGIN_ROUTE=/magic-login
LPL_LOGIN_ROUTE_ACTION=get
LPL_LOGIN_ROUTE_NAME=magic-login
LPL_LOGIN_ROUTE_EXPIRES=30
LPL_REDIRECT_ON_LOGIN=/
LPL_USER_GUARD=web
LPL_USE_ONCE=false
LPL_REQUIRE_CACHE_MARKER=false
LPL_CACHE_STORE=
LPL_INVALID_SIGNATURE_MESSAGE="Expired or Invalid Link"
```
`LPL_REMEMBER_LOGIN` is whether you want to remember the login (like the user checking Remember Me)

`LPL_LOGIN_ROUTE` is the route that points to the login function this package provides. Make sure you don't collide with one of your other routes.

`LPL_LOGIN_ROUTE_ACTION` is the HTTP verb the login route responds to, e.g. `get` or `post`. Defaults to `get`. If you use `post`, you'll need to exclude the route from CSRF verification — see [Laravel's CSRF documentation](https://laravel.com/docs/13.x/csrf#csrf-excluding-uris).

`LPL_LOGIN_ROUTE_NAME` is the name of the LPL_LOGIN_ROUTE. Again, make sure it doesn't collide with any of your existing route names.

`LPL_LOGIN_ROUTE_EXPIRES` is the number of minutes you want the link to be good for. I recommend you set the shortest value that makes sense for your use case.

`LPL_REDIRECT_ON_LOGIN` is where you want to send the user after they've logged in by clicking their magic link.

`LPL_USE_ONCE` is whether you want a link to expire after first use. When enabled, the link is consumed on first use and cannot be used again.

`LPL_REQUIRE_CACHE_MARKER` is whether a link must have a matching entry in the cache to be considered valid, which is what powers [invalidating links](#invalidating-links) before they expire. It defaults to `false` so that links generated before you adopted this feature (or before you upgraded across a version that introduced it) keep working based on their own signature and expiry alone. Turn it on if you need `invalidateForUser()` to actually revoke outstanding multi-use links — note that doing so also means a cleared or evicted cache will invalidate every outstanding link, so make sure your cache store is durable enough for your link lifetimes before enabling it. `LPL_USE_ONCE` links always check the cache marker regardless of this setting.

`LPL_CACHE_STORE` is the name of the cache store (as defined in your `config/cache.php`) that link markers are read from and written to. Leave it blank to use your app's default (`cache.default`). Set it to point markers at a specific store when you need them to survive things that don't affect link validity — a `cache:clear` on deploy, a Redis restart, or `maxmemory` eviction on your general-purpose cache — which matters most once `LPL_REQUIRE_CACHE_MARKER=true`, since that's when marker survival determines whether a link still works.

`LPL_INVALID_SIGNATURE_MESSAGE` is a custom message sent when we abort with a 401 status on an invalid or expired link. You can also add some custom logic on how to deal with invalid or expired links by handling `InvalidSignatureException` and `ExpiredSignatureException` in your `Handler.php` file.

### Invalidating Links

Links can be explicitly revoked before they expire — for example, after a user sets a password or changes their email.

```php
use Grosv\LaravelPasswordlessLogin\PasswordlessLogin;

// Revoke any outstanding magic link for a user
PasswordlessLogin::invalidateForUser($user);
```

Generating a new link for a user automatically clears any prior invalidation, so calling `generate()` is all you need to issue a fresh link.

> **Note:** `invalidateForUser()` only takes effect on multi-use links when `LPL_REQUIRE_CACHE_MARKER=true` (see [Configuration](#configuration)) — otherwise a link's own signature and expiry are all that's checked, and revocation is a no-op. With the marker required, magic links are tracked in the cache, so a cleared or evicted cache also invalidates every outstanding link — intentional, since a cleared cache is safer than silently reactivating a revoked link, but it means your cache store needs to be durable enough to outlive your longest-lived links. Use `LPL_CACHE_STORE` to point markers at a store dedicated to that purpose, separate from whatever your app flushes on deploy. `LPL_USE_ONCE` links check the cache marker regardless of this setting, since consuming a link has always relied on it.

### Events

The package dispatches events during the login flow that you can listen for, e.g. for auditing or alerting on suspicious activity:

| Event | Dispatched when | `$user` |
| --- | --- | --- |
| `Grosv\LaravelPasswordlessLogin\Events\LoginLinkSuccessful` | A valid, unexpired link successfully logs the user in. | Always present. |
| `Grosv\LaravelPasswordlessLogin\Events\LoginLinkExpired` | A correctly signed link is used after it has expired. | Always present. |
| `Grosv\LaravelPasswordlessLogin\Events\LoginLinkInvalid` | A request has an invalid or missing signature (e.g. tampered URL, or a URL missing its signed query parameters entirely). | May be `null` if the user couldn't be identified from the request. |

```php
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkInvalid;

class LogSuspiciousLoginAttempt
{
    public function handle(LoginLinkInvalid $event): void
    {
        // $event->user may be null
        logger()->warning('Invalid passwordless login attempt', [
            'user_id' => $event->user?->id,
        ]);
    }
}
```

### Reporting Issues

For security issues, please email me directly at `security@silentz.co`. For any other problems, use the issue tracker here.

### Contributing

I welcome the community's help with improving and maintaining all my packages. Just be nice to each other. Remember we're all just trying to do our best.
