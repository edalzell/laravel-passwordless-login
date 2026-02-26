<?php

namespace Tests;

use Grosv\LaravelPasswordlessLogin\LaravelPasswordlessLoginProvider;
use Illuminate\Support\Facades\Config;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadLaravelMigrations();
        Config::set('laravel-passwordless-login.user_model', 'Grosv\LaravelPasswordlessLogin\Models\User');
        Config::set('laravel-passwordless-login.redirect_on_success', '/laravel_passwordless_login_redirect_test_route');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
    }

    /**
     * add the package provider.
     *
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [LaravelPasswordlessLoginProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('app.key', 'base64:r0w0xC+mYYqjbZhHZ3uk1oH63VadA3RKrMW52OlIDzI=');
    }
}
