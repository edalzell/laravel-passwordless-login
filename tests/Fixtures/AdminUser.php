<?php

namespace Tests\Fixtures;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use PasswordlessLogin;

    protected $table = 'admins';

    protected $fillable = ['name', 'email', 'password'];

    protected function guardName(): Attribute
    {
        return Attribute::make(get: fn (): string => 'admin');
    }
}
