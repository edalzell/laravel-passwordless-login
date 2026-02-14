<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Contracts\Auth\Authenticatable as User;

/**
 * The class used by \Grosv\LaravelPasswordlessLogin\PasswordlessLoginFacade.
 *
 * Class PasswordlessLogin
 */
class PasswordlessLoginManager
{
    /**
     * @var \Grosv\LaravelPasswordlessLogin\LoginUrl
     */
    private $loginUrl;

    /**
     * This assigns the login url to the given user.
     *
     *
     * @return $this
     */
    public function forUser(User $user)
    {
        $this->loginUrl = new LoginUrl($user);

        return $this;
    }

    /**
     * Sets redirect URL for the Facade.
     *
     *
     * @return $this
     */
    public function setRedirectUrl(string $redirectUrl): self
    {
        $this->loginUrl->setRedirectUrl($redirectUrl);

        return $this;
    }

    /**
     * This generates the URL.
     *
     * @return string signed login url
     */
    public function generate()
    {
        return $this->loginUrl->generate();
    }
}
