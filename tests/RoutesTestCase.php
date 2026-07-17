<?php

namespace Tests;

class RoutesTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('laravel-passwordless-login.login_route_action', 'post');
    }
}
