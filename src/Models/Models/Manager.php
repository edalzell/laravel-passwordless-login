<?php

namespace Grosv\LaravelPasswordlessLogin\Models\Models;

use Grosv\LaravelPasswordlessLogin\Traits\PasswordlessLogin;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Manager extends Authenticatable
{
    use PasswordlessLogin;

    protected $table = 'users';

    protected $fillable = ['name', 'email', 'password', 'phone'];
}
