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
    private LoginUrl $loginUrl;

    /**
     * This assigns the login url to the given user.
     */
    public function forUser(User $user): self
    {
        $this->loginUrl = new LoginUrl($user);

        return $this;
    }

    /**
     * Sets redirect URL for the Facade.
     */
    public function setRedirectUrl(string $redirectUrl): self
    {
        $this->loginUrl->setRedirectUrl($redirectUrl);

        return $this;
    }

    /**
     * This generates the URL.
     */
    public function generate(): string
    {
        return $this->loginUrl->generate();
    }

    public function invalidateForUser(User $user): void
    {
        cache()->forget(UserClass::cacheKey($user));
    }
}
