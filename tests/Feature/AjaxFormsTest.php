<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * The JSON side of the forms sent by public/js/ajax-form.js.
 */
class AjaxFormsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_and_admin_forms_are_ajax_forms()
    {
        $this->get('/login')->assertSee('data-ajax-form', false)->assertSee('data-error-for="email"', false);
        $this->get('/password/reset')->assertSee('data-ajax-form', false);
        $this->get('/password/reset/some-token')->assertSee('data-ajax-form', false);

        $this->actingAs(User::factory()->create())
            ->get('/dashboard/settings')
            ->assertSee('data-ajax-form', false)
            ->assertSee('name="_method" value="PATCH"', false)
            ->assertSee('data-error-for="password"', false);
    }

    public function test_an_ajax_login_gets_the_dashboard_url()
    {
        $user = User::factory()->create();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertOk()
            ->assertExactJson(['redirect' => url('/dashboard')]);

        $this->assertAuthenticatedAs($user);
    }

    public function test_an_ajax_login_goes_back_to_the_page_asked_for()
    {
        $user = User::factory()->create();
        $this->get('/dashboard/settings')->assertRedirect('/login');

        $this->postJson('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertExactJson(['redirect' => url('/dashboard/settings')]);
    }

    public function test_a_wrong_ajax_login_gets_a_field_error()
    {
        $user = User::factory()->create();

        $this->postJson('/login', ['email' => $user->email, 'password' => 'wrong'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_an_ajax_reset_link_request_gets_a_message()
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->postJson('/password/email', ['email' => $user->email])
            ->assertOk()
            ->assertJsonStructure(['message']);

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_an_ajax_reset_goes_to_the_dashboard_with_the_message()
    {
        $user = User::factory()->create();
        $token = Password::broker()->createToken($user);

        $this->postJson('/password/reset', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password-1',
            'password_confirmation' => 'new-password-1',
        ])->assertOk()->assertJson(['redirect' => url('/dashboard')]);

        $this->assertTrue(Hash::check('new-password-1', $user->fresh()->password));
        $this->get('/dashboard')->assertSee('Your password has been reset.', false);
    }

    public function test_ajax_settings_save_and_reload_with_the_message()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/dashboard/settings', ['name' => 'New Name', 'email' => $user->email])
            ->assertOk()
            ->assertExactJson(['message' => 'Account settings updated successfully.', 'redirect' => route('settings')]);

        $this->assertSame('New Name', $user->fresh()->name);
        $this->get('/dashboard/settings')->assertSee('Account settings updated successfully.');
    }

    public function test_ajax_settings_errors_come_back_per_field()
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patchJson('/dashboard/settings', ['name' => '', 'email' => 'bad', 'password' => 'short', 'password_confirmation' => 'other'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_the_layouts_load_the_theme_files_that_exist()
    {
        $pages = [
            $this->get('/login')->getContent(),
            $this->actingAs(User::factory()->create())->get('/dashboard')->getContent(),
        ];

        foreach ($pages as $html) {
            preg_match_all('#(?:href|src)="' . preg_quote(url('/'), '#') . '/((?:admin|css|js)/[^"]+)"#', $html, $matches);
            $this->assertNotEmpty($matches[1]);

            foreach ($matches[1] as $path) {
                $this->assertFileExists(public_path($path));
            }
        }
    }
}
