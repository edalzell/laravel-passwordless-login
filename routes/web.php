<?php

use Grosv\LaravelPasswordlessLogin\HandleAuthenticatedUsers;
use Grosv\LaravelPasswordlessLogin\LaravelPasswordlessLoginController;
use Illuminate\Support\Facades\Route;

Route::match(
    config('laravel-passwordless-login.login_route_action', 'get'),
    config('laravel-passwordless-login.login_route').'/{uid}',
    [LaravelPasswordlessLoginController::class, 'login']
)->middleware(config('laravel-passwordless-login.middleware', ['web', HandleAuthenticatedUsers::class]))->name(config('laravel-passwordless-login.login_route_name'));
