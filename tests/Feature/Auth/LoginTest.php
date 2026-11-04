<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_renders()
    {
        $this->get('/login')->assertOk()->assertSee('name="email"');
    }

    public function test_correct_login_goes_to_the_dashboard()
    {
        $user = factory(User::class)->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);
    }

    public function test_wrong_password_fails()
    {
        $user = factory(User::class)->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'wrong-password'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_guest_opening_the_dashboard_is_sent_to_login()
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_logged_in_user_opening_login_is_sent_to_the_dashboard()
    {
        $this->actingAs(factory(User::class)->create())
            ->get('/login')
            ->assertRedirect('/dashboard');
    }

    public function test_logout_ends_the_session()
    {
        $this->actingAs(factory(User::class)->create())
            ->post('/logout')
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
