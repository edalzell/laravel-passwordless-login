<?php

namespace Grosv\LaravelPasswordlessLogin;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Http\Request;

/**
 * Service class to keep the controller clean.
 */
class PasswordlessLoginService
{
    private string $cacheKey;

    public ?Authenticatable $user;

    public function __construct(
        private readonly Request $request,
        private readonly AuthFactory $auth,
        private readonly CacheRepository $cache,
        private readonly ConfigRepository $config,
    ) {
        $this->user = $this->getUser();
        $this->cacheKey = $this->requestValue('user_type').$this->requestValue('uid');
    }

    /**
     * Checks if this use class uses the PasswordlessLogable trait.
     */
    public function usesTrait(): bool
    {
        $traits = class_uses($this->user, true);

        return in_array(PasswordlessLogin::class, $traits);
    }

    public function getUser(): ?Authenticatable
    {
        if (! $this->request->has('user_type')) {
            return null;
        }

        $userClass = UserClass::fromSlug($this->requestValue('user_type'));
        $guard = (new $userClass)->guard_name ?? $this->config->get('laravel-passwordless-login.user_guard');

        return $this->auth->guard($guard)
            ->getProvider()
            ->retrieveById($this->requestValue('uid'));
    }

    public function cacheRequest(Request $request): void
    {
        $this->consumeRequest();
    }

    public function consumeRequest(): void
    {
        $loginOnce = $this->usesTrait()
            ? $this->user->login_use_once
            : $this->config->get('laravel-passwordless-login.login_use_once');

        if ($loginOnce) {
            $this->cache->forget($this->cacheKey);
        }
    }

    public function requestIsNew(): bool
    {
        return $this->cache->has($this->cacheKey);
    }

    private function requestValue(string $key): string
    {
        return (string) ($this->request->route($key) ?? $this->request->input($key, ''));
    }
}
