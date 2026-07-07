<?php

namespace Tests\Fixtures;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use PasswordlessLogin;

    protected $table = 'admins';

    protected $fillable = ['name', 'email', 'password'];

    public function getGuardNameAttribute(): string
    {
        return 'admin';
    }
}
