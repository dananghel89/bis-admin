<?php

namespace Tests\Feature\Auth;

use App\Models\User;
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
        $this->get('/password/reset')->assertOk()->assertSee('name="email"', false);
    }

    public function test_requesting_a_reset_sends_the_reset_notification()
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_requests_are_throttled()
    {
        // At most 3 requests per 10 minutes from one IP. Each request uses another user, because
        // the password broker also limits repeat links for the same email (60 seconds).
        Notification::fake();
        $users = User::factory(4)->create();

        foreach ($users->take(3) as $user) {
            $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');
            Notification::assertSentTo($user, ResetPassword::class);
        }
        $this->post('/password/email', ['email' => $users[3]->email])->assertStatus(429);

        Notification::assertNotSentTo($users[3], ResetPassword::class);
    }

    public function test_the_same_email_gets_one_link_per_minute()
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');
        $this->post('/password/email', ['email' => $user->email])->assertSessionHasErrors('email');

        Notification::assertSentToTimes($user, ResetPassword::class, 1);
    }

    public function test_reset_throttle_is_separate_from_sign_up()
    {
        // The throttle key includes the route, so sign-up attempts don't use up reset requests
        Notification::fake();
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/register', ['name' => 'X']);
        }

        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');
    }

    public function test_reset_screen_renders()
    {
        $this->get('/password/reset/some-token')->assertOk()->assertSee('name="token"', false);
    }

    public function test_reset_screen_prefills_the_email_from_the_link()
    {
        $this->get('/password/reset/some-token?email=jane%40example.com')
            ->assertOk()
            ->assertSee('value="jane@example.com"', false)
            ->assertDontSee('value="1"', false);
    }

    public function test_valid_token_resets_the_password()
    {
        $user = User::factory()->create();
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
