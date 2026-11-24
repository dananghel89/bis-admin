<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CreateAdminCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_asks_for_everything_and_creates_an_admin()
    {
        $this->artisan('app:create-admin')
            ->expectsQuestion('Name', 'Ana Staff')
            ->expectsQuestion('Email address', 'ana@example.com')
            ->expectsQuestion('Password (at least 8 characters)', 'secret-pass')
            ->expectsQuestion('Repeat the password', 'secret-pass')
            ->expectsOutputToContain('Admin ana@example.com created.')
            ->assertSuccessful();

        $user = User::sole();
        $this->assertSame('Ana Staff', $user->name);
        $this->assertSame(User::ADMIN, $user->role);
        $this->assertTrue(Hash::check('secret-pass', $user->password));
    }

    public function test_name_and_email_can_be_options_but_the_password_is_always_asked()
    {
        $this->artisan('app:create-admin', ['--name' => 'Ana', '--email' => 'ana@example.com'])
            ->expectsQuestion('Password (at least 8 characters)', 'secret-pass')
            ->expectsQuestion('Repeat the password', 'secret-pass')
            ->assertSuccessful();

        $this->assertSame(1, User::where('role', User::ADMIN)->count());
    }

    public function test_bad_input_creates_nothing()
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $this->artisan('app:create-admin', ['--name' => 'Ana', '--email' => 'taken@example.com'])
            ->expectsQuestion('Password (at least 8 characters)', 'short')
            ->expectsQuestion('Repeat the password', 'other')
            ->expectsOutputToContain('The email has already been taken.')
            ->assertFailed();

        $this->assertSame(0, User::where('role', User::ADMIN)->count());
    }
}
