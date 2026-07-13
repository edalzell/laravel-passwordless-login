<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Str;

class UserClass
{
    public static function cacheKey(Authenticatable $user): string
    {
        return static::toSlug(get_class($user)).$user->getAuthIdentifier();
    }

    // Pre-upgrade entries were a bare `true`; treat those as empty rather than erroring.
    public static function activeLinks(string $key): array
    {
        $activeLinks = cache()->get($key, []);

        return is_array($activeLinks) ? $activeLinks : [];
    }

    public static function toSlug(string $class): string
    {
        $pieces = array_map(function (string $piece): string {
            return Str::snake($piece);
        }, explode('\\', $class));

        return implode('-', $pieces);
    }

    public static function fromSlug(string $slug): string
    {
        $pieces = array_map(function (string $piece): string {
            return ucfirst(Str::studly($piece));
        }, explode('-', $slug));

        return implode('\\', $pieces);
    }
}
