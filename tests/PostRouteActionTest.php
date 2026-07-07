<?php

use Faker\Factory as Faker;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\Models\User;
use Illuminate\Support\Str;
use Tests\PostRouteActionTestCase;

uses(PostRouteActionTestCase::class);

beforeEach(function () {
    $faker = Faker::create();
    $this->user = User::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ]);

    $this->url = (new LoginUrl($this->user))->generate();
});

test('a configured post route action will log user in', function () {
    $this->assertGuest();
    $response = $this->followingRedirects()->post($this->url);
    $response->assertSuccessful();
    $this->assertAuthenticatedAs($this->user);
});
