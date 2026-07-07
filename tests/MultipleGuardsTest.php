<?php

namespace Tests;

use Faker\Factory as Faker;
use Grosv\LaravelPasswordlessLogin\LoginUrl;
use Grosv\LaravelPasswordlessLogin\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\Test;
use Tests\Fixtures\AdminUser;

class MultipleGuardsTest extends TestCase
{
    private $admin;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    #[Test]
    public function the_guard_configured_on_the_user_model_is_used_to_retrieve_the_user()
    {
        $this->assertSame($this->user->id, $this->admin->id);

        $url = (new LoginUrl($this->admin))->generate();

        $this->assertGuest('admin');
        $this->assertGuest('web');

        $this->get($url);

        $this->assertAuthenticatedAs($this->admin, 'admin');
        $this->assertGuest('web');
    }

    #[Test]
    public function a_user_without_a_custom_guard_still_uses_the_default_guard()
    {
        $url = (new LoginUrl($this->user))->generate();

        $this->assertGuest('web');

        $this->followingRedirects()->get($url);

        $this->assertAuthenticatedAs($this->user, 'web');
        $this->assertGuest('admin');
    }
}
