<?php

use Faker\Factory as Faker;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\Models\User;
use Grosv\LaravelPasswordlessLogin\UserClass;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Fixtures\AdminUser;
use Tests\TestCase;

uses(TestCase::class);

beforeEach(function () {
    Config::set('auth.guards.admin', ['driver' => 'session', 'provider' => 'admins']);
    Config::set('auth.providers.admins', ['driver' => 'eloquent', 'model' => AdminUser::class]);

    Schema::create('admins', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('email')->unique();
        $table->timestamp('email_verified_at')->nullable();
        $table->string('password');
        $table->rememberToken();
        $table->timestamps();
    });

    $faker = Faker::create();

    // Deliberately create the web user first, so it shares its auto-increment id
    // with the admin user created below. This proves the correct guard/table is
    // used to retrieve the user rather than whichever "users" row has the same id.
    $this->user = User::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ]);

    $this->admin = AdminUser::create([
        'name' => $faker->name,
        'email' => $faker->unique()->safeEmail,
        'email_verified_at' => now(),
        'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
        'remember_token' => Str::random(10),
    ]);
});

test('the guard configured on the user model is used to retrieve the user', function () {
    expect($this->admin->id)->toBe($this->user->id);

    $url = (new LoginUrl($this->admin))->generate();

    $this->assertGuest('admin');
    $this->assertGuest('web');

    $this->get($url);

    $this->assertAuthenticatedAs($this->admin, 'admin');
    $this->assertGuest('web');
});

test('a user without a custom guard still uses the default guard', function () {
    $url = (new LoginUrl($this->user))->generate();

    $this->assertGuest('web');

    $this->followingRedirects()->get($url);

    $this->assertAuthenticatedAs($this->user, 'web');
    $this->assertGuest('admin');
});

test('a json body cannot override the signed uid', function () {
    $url = (new LoginUrl($this->user))->generate();
    $other = User::create([
        'name' => 'Other',
        'email' => 'other@example.com',
        'password' => 'secret',
    ]);

    $this->json('GET', $url, ['uid' => $other->id]);

    $this->assertAuthenticatedAs($this->user, 'web');
});

test('a json body cannot override the signed user type', function () {
    $url = (new LoginUrl($this->user))->generate();

    $this->json('GET', $url, ['user_type' => UserClass::toSlug(AdminUser::class)]);

    $this->assertAuthenticatedAs($this->user, 'web');
    $this->assertGuest('admin');
});

test('a json body cannot override the signed expires', function () {
    Config::set('laravel-passwordless-login.login_use_once', true);

    $url = (new LoginUrl($this->user))->generate();
    $this->travel(1)->minutes();
    $otherExpires = Str::of((new LoginUrl($this->user))->generate())->match('/expires=(\d+)/')->toString();

    $this->get($url);
    $this->assertAuthenticatedAs($this->user, 'web');
    Auth::logout();

    $this->json('GET', $url, ['expires' => $otherExpires]);

    $this->assertGuest('web');
});
