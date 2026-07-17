<?php

use Grosv\LaravelPasswordlessLogin\UserClass;

test('make from class', function () {
    $slug = UserClass::toSlug('HelloWorld\\ModelsFolder\\User');

    expect($slug)->toEqual('hello_world-models_folder-user');
});

test('make from slug', function () {
    $userClass = UserClass::fromSlug('hello_world-models_folder-user');

    expect($userClass)->toEqual('HelloWorld\\ModelsFolder\\User');
});
