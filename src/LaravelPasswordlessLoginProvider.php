<?php

namespace Grosv\LaravelPasswordlessLogin;

use Illuminate\Support\ServiceProvider;

class LaravelPasswordlessLoginProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadRoutesFrom(__DIR__.'/routes.php');
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/config.php' => config_path('laravel-passwordless-login.php'),
            ], 'passwordless-login-config');
        }
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/config.php', 'laravel-passwordless-login');

        $this->app->singleton('passwordless-login', function ($app) {
            return new PasswordlessLoginManager;
        });
    }
}
