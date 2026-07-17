<?php

namespace Tests\Fixtures\Models;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use PasswordlessLogin;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'phone'];
}
