# Laravel Passwordless Login
### A simple, safe magic login link generator for Laravel
![build status](https://github.com/grosv/laravel-passwordless-login/actions/workflows/test.yml/badge.svg)

This package provides a temporary signed route that logs in a user. What it does not provide is a way of actually sending the link to the route to the user. This is because I don't want to make any assumptions about how you communicate with your users.

### Installation
```shell script
composer require grosv/laravel-passwordless-login
```
### :film_strip: Video Tutorial 
Video tutorial can be found [here](https://youtu.be/EIksYsX05Tg)

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

### Configuration
You can publish the config file or just set the values you want to use in your .env file:
```dotenv
LPL_USER_MODEL=App\User
LPL_REMEMBER_LOGIN=false
LPL_LOGIN_ROUTE=/magic-login
LPL_LOGIN_ROUTE_NAME=magic-login
LPL_LOGIN_ROUTE_EXPIRES=30
LPL_REDIRECT_ON_LOGIN=/
LPL_USER_GUARD=web
LPL_USE_ONCE=false
LPL_INVALID_SIGNATURE_MESSAGE="Expired or Invalid Link"
```
`LPL_USER_MODEL` is the the authenticatable model you are logging in (usually App\User)

`LPL_REMEMBER_LOGIN` is whether you want to remember the login (like the user checking Remember Me)

`LPL_LOGIN_ROUTE` is the route that points to the login function this package provides. Make sure you don't collide with one of your other routes.

`LPL_LOGIN_ROUTE_NAME` is the name of the LPL_LOGIN_ROUTE. Again, make sure it doesn't collide with any of your existing route names.

`LPL_LOGIN_ROUTE_EXPIRES` is the number of minutes you want the link to be good for. I recommend you set the shortest value that makes sense for your use case.

`LPL_REDIRECT_ON_LOGIN` is where you want to send the user after they've logged in by clicking their magic link.

`LPL_USE_ONCE` is whether you want a link to expire after first use (uses cache to store used links)

`LPL_INVALID_SIGNATURE_MESSAGE` is a custom message sent when we abort with a 401 status on an invalid or expired link. You can also add some custom logic on how to deal with invalid or expired links by handling `InvalidSignatureException` and `ExpiredSignatureException` in your `Handler.php` file. 

### Reporting Issues

For security issues, please email me directly at ed@gros.co. For any other problems, use the issue tracker here.

### Contributing

I welcome the community's help with improving and maintaining all my packages. Just be nice to each other. Remember we're all just trying to do our best.
