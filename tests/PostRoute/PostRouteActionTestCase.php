<?php

namespace Tests\PostRoute;

use Tests\TestCase;

class PostRouteActionTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);
        $app['config']->set('laravel-passwordless-login.login_route_action', 'post');
    }
}
