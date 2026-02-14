<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Contracts\Auth\Authenticatable as User;
use Illuminate\Support\Facades\URL;

class LoginUrl
{
    /**
     * @var User
     */
    private $user;
    /**
     * @var string
     */
    private $route_name;
    /**
     * @var \Carbon\Carbon
     */
    private $route_expires;
    /**
     * @var string
     */
    private $redirect_url;

    public function __construct(User $user)
    {
        $this->user = $user;

        $this->route_expires = now()->addMinutes($this->user->login_route_expires_in ?? config('laravel-passwordless-login.login_route_expires'));

        $this->route_name = config('laravel-passwordless-login.login_route_name');
    }

    public function setRedirectUrl(string $redirectUrl)
    {
        $this->redirect_url = $redirectUrl;
    }

    public function generate()
    {
        return URL::temporarySignedRoute(
            $this->route_name,
            $this->route_expires,
            [
                'uid'           => $this->user->getAuthIdentifier(),
                'redirect_to'   => $this->redirect_url,
                'user_type'     => UserClass::toSlug(get_class($this->user)),
            ]
        );
    }
}
