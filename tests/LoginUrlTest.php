<?php

use Carbon\Carbon;
use Faker\Factory as Faker;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkExpired;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkInvalid;
use Grosv\LaravelPasswordlessLogin\Events\LoginLinkSuccessful;
use Grosv\LaravelPasswordlessLogin\Exceptions\ExpiredSignatureException;
use Grosv\LaravelPasswordlessLogin\Exceptions\InvalidSignatureException;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\Models\User;
use Grosv\LaravelPasswordlessLogin\PasswordlessLogin;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Tests\Fixtures\Models\User as ModelUser;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    $faker = Faker::create();
    $this->user = User::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ]);

    $this->model_user = ModelUser::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ]);

    Carbon::setTestNow();

    $generator = new LoginUrl($this->user);
    $this->url = $generator->generate();
    [$route, $uid] = explode('/', ltrim(parse_url($this->url)['path'], '/'));
    $expires = explode('=', explode('&', explode('?', $this->url)[1])[0])[1];

    $this->route = $route;
    $this->expires = $expires;
    $this->uid = $uid;
});

test('can create default signed login url', function () {
    Carbon::setTestNow(now());
    expect($this->expires)->toEqual(Carbon::now()->addMinutes(config('laravel-passwordless-login.login_route_expires'))->timestamp);
    expect($this->uid)->toEqual($this->user->id);
    expect($this->route)->toEqual(config('laravel-passwordless-login.login_route_name'));
});

test('a signed request will log user in and redirect', function () {
    Event::fake();
    $this->withoutExceptionHandling();
    $this->assertGuest();
    $response = $this->followingRedirects()->get($this->url);
    Event::assertDispatched(LoginLinkSuccessful::class);
    $this->assertAuthenticatedAs($this->user);
    $response->assertSuccessful();
    Auth::logout();
    $this->assertGuest();
});

test('an unsigned request will not log user in', function () {
    Event::fake();
    $unsigned = explode('?', $this->url)[0];
    $this->assertGuest();

    $this->get($unsigned);
    Event::assertNotDispatched(LoginLinkSuccessful::class);
    Event::assertDispatched(LoginLinkInvalid::class);
    $this->assertGuest();

    $this->withoutExceptionHandling();
    $this->expectException(InvalidSignatureException::class);
    $this->get($unsigned);
});

test('an invalid signature request will not log user in', function () {
    Event::fake();

    // Check 401 is returned
    $this->assertGuest();
    $response = $this->get($this->url.'tampered');
    Event::assertNotDispatched(LoginLinkSuccessful::class);
    $response->assertStatus(401);
    $this->assertGuest();

    // Check correct exception is thrown
    $this->withoutExceptionHandling();
    $this->expectException(InvalidSignatureException::class);
    $this->get($this->url.'tampered');
});

test('allows override of post login redirect', function () {
    $generator = new LoginUrl($this->user);
    $generator->setRedirectUrl('/laravel_passwordless_login_redirect_overridden_route');
    $this->url = $generator->generate();
    $response = $this->followingRedirects()->get($this->url);
    $response->assertStatus(200);
    $this->assertAuthenticatedAs($this->user);
});

test('allows alternative auth model', function () {
    $generator = new LoginUrl($this->model_user);
    $generator->setRedirectUrl('/laravel_passwordless_login_redirect_overridden_route');
    $this->url = $generator->generate();
    $response = $this->followingRedirects()->get($this->url);
    $response->assertSuccessful();
    $response->assertSee($this->model_user->name, false);
    $this->assertAuthenticatedAs($this->model_user);
});

test('an expired request will not log user in', function () {
    Event::fake();
    Carbon::setTestNow(Carbon::now()->addMinutes(config('laravel-passwordless-login.login_route_expires') + 1));

    // Make sure 401 is returned
    $this->assertGuest();
    $response = $this->get($this->url);
    $response->assertStatus(401);
    Event::assertNotDispatched(LoginLinkSuccessful::class);
    Event::assertDispatched(LoginLinkExpired::class);
    $this->assertGuest();

    // Make sure ExpiredSignatureException is thrown
    $this->withoutExceptionHandling();
    $this->expectException(ExpiredSignatureException::class);
    $this->get($this->url);
});

test('an authenticated user is redirected correctly', function () {
    $this->actingAs($this->user);
    $response = $this->get($this->url);
    $response->assertRedirect(config('laravel-passwordless-login.redirect_on_success'));
});

test('an authenticated user with redirect on url is redirected correctly', function () {
    $this->actingAs($this->user);
    $response = $this->get($this->url.'&redirect_to=/happy_path');
    $response->assertRedirect('/happy_path');
});

test('a link invalidated via facade cannot be used', function () {
    Config::set('laravel-passwordless-login.require_cache_marker', true);
    PasswordlessLogin::invalidateForUser($this->user);
    $this->assertGuest();

    $this->get($this->url);
    $this->assertGuest();

    $this->withoutExceptionHandling();
    $this->expectException(InvalidSignatureException::class);
    $this->get($this->url);
});

test('generating a new link clears invalidation', function () {
    PasswordlessLogin::invalidateForUser($this->user);
    $this->url = (new LoginUrl($this->user))->generate();

    $this->assertGuest();
    $this->followingRedirects()->get($this->url);
    $this->assertAuthenticatedAs($this->user);
});

test('a use once link cannot be used twice', function () {
    Config::set('laravel-passwordless-login.login_use_once', true);

    $this->assertGuest();
    $this->followingRedirects()->get($this->url);
    $this->assertAuthenticatedAs($this->user);
    Auth::logout();

    $this->withoutExceptionHandling();
    $this->expectException(InvalidSignatureException::class);
    $this->get($this->url);
});

test('a multi use link can be used multiple times', function () {
    $this->assertGuest();
    $this->followingRedirects()->get($this->url);
    $this->assertAuthenticatedAs($this->user);
    Auth::logout();

    $this->followingRedirects()->get($this->url);
    $this->assertAuthenticatedAs($this->user);
    Auth::logout();
});
