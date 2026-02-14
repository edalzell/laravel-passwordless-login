<?php

namespace Tests;

use Carbon\Carbon;
use Faker\Factory as Faker;
use Grosv\LaravelPasswordlessLogin\Exceptions\ExpiredSignatureException;
use Grosv\LaravelPasswordlessLogin\Exceptions\InvalidSignatureException;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\Models\Models\User as ModelUser;
use Grosv\LaravelPasswordlessLogin\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;

class SignedUrlTest extends TestCase
{
    protected $user;

    private $url;

    private $route;

    private $expires;

    private $uid;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    #[Test]
    public function can_create_default_signed_login_url()
    {
        $this->assertEquals(Carbon::now()->addMinutes(config('laravel-passwordless-login.login_route_expires'))->timestamp, $this->expires);
        $this->assertEquals($this->user->id, $this->uid);
        $this->assertEquals(config('laravel-passwordless-login.login_route_name'), $this->route);
    }

    #[Test]
    public function a_signed_request_will_log_user_in_and_redirect()
    {
        $this->withoutExceptionHandling();
        $this->assertGuest();
        $response = $this->followingRedirects()->get($this->url);
        $this->assertAuthenticatedAs($this->user);
        $response->assertSuccessful();
        Auth::logout();
        $this->assertGuest();
    }

    #[Test]
    public function an_unsigned_request_will_not_log_user_in()
    {
        $unsigned = explode('?', $this->url)[0];
        $this->assertGuest();
        $this->withoutExceptionHandling();
        $this->expectException(InvalidSignatureException::class);
        $this->get($unsigned);
        $this->assertGuest();
    }

    #[Test]
    public function an_invalid_signature_request_will_not_log_user_in()
    {
        // Check 401 is returned
        $this->assertGuest();
        $response = $this->get($this->url.'tampered');
        $response->assertStatus(401);
        $this->assertGuest();

        // Check correct exception is thrown
        $this->withoutExceptionHandling();
        $this->expectException(InvalidSignatureException::class);
        $this->get($this->url.'tampered');
    }

    #[Test]
    public function allows_override_of_post_login_redirect()
    {
        $generator = new LoginUrl($this->user);
        $generator->setRedirectUrl('/laravel_passwordless_login_redirect_overridden_route');
        $this->url = $generator->generate();
        $response = $this->followingRedirects()->get($this->url);
        $response->assertStatus(200);
        $this->assertAuthenticatedAs($this->user);
    }

    #[Test]
    public function allows_alternative_auth_model()
    {
        $generator = new LoginUrl($this->model_user);
        $generator->setRedirectUrl('/laravel_passwordless_login_redirect_overridden_route');
        $this->url = $generator->generate();
        $response = $this->followingRedirects()->get($this->url);
        $response->assertSuccessful();
        $response->assertSee($this->model_user->name);
        $this->assertAuthenticatedAs($this->model_user);
    }

    // #[Test]
    // public function an_expired_request_will_not_log_user_in()
    // {
    //     sleep(config('laravel-passwordless-login.login_route_expires') + 1);

    //     // Make sure 401 is returned
    //     $this->assertGuest();
    //     $response = $this->get($this->url);
    //     $response->assertStatus(401);
    //     $this->assertGuest();

    //     // Make sure ExpiredSignatureException is thrown
    //     $this->withoutExceptionHandling();
    //     $this->expectException(ExpiredSignatureException::class);
    //     $this->get($this->url);
    //     $this->assertGuest();
    // }

    #[Test]
    public function an_authenticated_user_is_redirected_correctly()
    {
        $this->actingAs($this->user);
        $response = $this->get($this->url);
        $response->assertRedirect(config('laravel-passwordless-login.redirect_on_success'));
    }

    #[Test]
    public function an_authenticated_user_with_redirect_on_url_is_redirected_correctly()
    {
        $this->actingAs($this->user);
        $response = $this->get($this->url.'&redirect_to=/happy_path');
        $response->assertRedirect('/happy_path');
    }
}
