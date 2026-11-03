<?php

use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Illuminate\Support\Facades\Config;
use Tests\Fixtures\Models\User;
use Tests\TestCase;

uses(TestCase::class);

test('a json body cannot override the signed redirect on success', function () {
    Config::set('auth.providers.users.model', User::class);

    $user = User::create([
        'name' => 'Trait User',
        'email' => 'trait@example.com',
        'password' => 'secret',
    ]);

    $response = $this->json('GET', (new LoginUrl($user))->generate(), ['redirect_to' => 'https://attacker.example']);

    $response->assertRedirect(config('laravel-passwordless-login.redirect_on_success'));
});
