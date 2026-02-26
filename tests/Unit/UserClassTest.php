<?php

namespace Tests\Unit;

use Grosv\LaravelPasswordlessLogin\UserClass;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UserClassTest extends TestCase
{
    #[Test]
    public function make_from_class()
    {
        $slug = UserClass::toSlug('HelloWorld\\ModelsFolder\\User');

        $this->assertEquals('hello_world-models_folder-user', $slug);
    }

    #[Test]
    public function make_from_slug()
    {
        $userClass = UserClass::fromSlug('hello_world-models_folder-user');

        $this->assertEquals('HelloWorld\\ModelsFolder\\User', $userClass);
    }
}
