<?php

namespace Grosv\LaravelPasswordlessLogin;

use Carbon\Carbon;
use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Service class to keep the controller clean.
 */
class PasswordlessLoginService
{
    public ?Authenticatable $user;

    public function __construct()
    {
        $this->user = $this->getUser();
    }

    private function cacheKey(): string
    {
        return request('user_type').request('uid');
    }

    private function expires(): int
    {
        return (int) request('expires');
    }

    private function activeLinks(): array
    {
        return UserClass::activeLinks($this->cacheKey());
    }

    private function loginOnce(): bool
    {
        return $this->usesTrait()
            ? $this->user->login_use_once
            : config('laravel-passwordless-login.login_use_once');
    }

    /**
     * Checks if this use class uses the PasswordlessLogable trait.
     */
    public function usesTrait(): bool
    {
        $traits = class_uses_recursive($this->user);

        return in_array(PasswordlessLogin::class, $traits);
    }

    public function getUser(): ?Authenticatable
    {
        if (! request()->has('user_type')) {
            return null;
        }

        $userClass = UserClass::fromSlug(request('user_type'));
        $guard = (new $userClass)->guard_name ?? config('laravel-passwordless-login.user_guard');

        return Auth::guard($guard)
            ->getProvider()
            ->retrieveById(request('uid'));
    }

    public function cacheRequest(Request $request): void
    {
        $this->consumeRequest();
    }

    public function consumeRequest(): void
    {
        if (! $this->loginOnce()) {
            return;
        }

        $activeLinks = $this->activeLinks();
        unset($activeLinks[$this->expires()]);

        if (empty($activeLinks)) {
            cache()->forget($this->cacheKey());

            return;
        }

        cache()->put(
            $this->cacheKey(),
            $activeLinks,
            Carbon::createFromTimestamp(max(array_keys($activeLinks)))
        );
    }

    public function requestIsNew(): bool
    {
        if (! $this->loginOnce() && ! config('laravel-passwordless-login.require_cache_marker', false)) {
            return true;
        }

        return array_key_exists($this->expires(), $this->activeLinks());
    }
}
