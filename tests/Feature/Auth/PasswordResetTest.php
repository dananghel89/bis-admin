<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\Facades\Notification;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_forgot_password_page_renders()
    {
        $this->get('/password/reset')->assertOk()->assertSee('name="email"');
    }

    public function test_requesting_a_reset_sends_the_reset_notification()
    {
        Notification::fake();
        $user = factory(User::class)->create();

        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_screen_renders()
    {
        $this->get('/password/reset/some-token')->assertOk()->assertSee('name="token"');
    }

    public function test_valid_token_resets_the_password()
    {
        $user = factory(User::class)->create();
        $token = Password::broker()->createToken($user);

        $this->post('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-secret-1',
            'password_confirmation' => 'new-secret-1',
        ])->assertRedirect('/dashboard');

        $this->assertTrue(Hash::check('new-secret-1', $user->fresh()->password));
    }
}
