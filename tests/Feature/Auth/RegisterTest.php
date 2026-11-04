<?php

namespace Tests\Feature\Auth;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A valid sign-up form.
     *
     * @return array
     */
    protected function validData()
    {
        return [
            'name' => 'New User',
            'email' => 'new.user@example.com',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'terms' => '1',
        ];
    }

    /**
     * Set the "membership" option and drop the cached options.
     *
     * @param string $value
     * @return void
     */
    protected function setMembership($value)
    {
        DB::table('options')->where('name', 'membership')->update(['value' => $value]);
        app()->forgetInstance('bis.options');
    }

    public function test_form_renders_when_membership_is_on()
    {
        $this->get('/register')->assertOk()->assertSee('name="terms"');
    }

    public function test_valid_sign_up_creates_a_logged_in_subscriber()
    {
        $this->post('/register', $this->validData())->assertRedirect('/dashboard');

        $user = User::where('email', 'new.user@example.com')->first();
        $this->assertNotNull($user);
        $this->assertSame(User::SUBSCRIBER, (int) $user->role);
        $this->assertAuthenticatedAs($user);
    }

    public function test_terms_are_required()
    {
        $data = $this->validData();
        unset($data['terms']);

        $this->post('/register', $data)->assertSessionHasErrors('terms');
        $this->assertSame(0, User::count());
    }

    public function test_register_redirects_home_when_membership_is_off()
    {
        $this->setMembership('0');

        $this->get('/register')->assertRedirect('/');
        $this->post('/register', $this->validData())->assertRedirect('/');
        $this->assertSame(0, User::count());
    }

    public function test_login_page_hides_the_register_link_when_membership_is_off()
    {
        $this->get('/login')->assertSee(url('/register'));

        $this->setMembership('0');

        $this->get('/login')->assertDontSee(url('/register'));
    }
}
