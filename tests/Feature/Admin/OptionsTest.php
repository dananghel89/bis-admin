<?php

namespace Tests\Feature\Admin;

use App\User;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OptionsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var \App\User
     */
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = factory(User::class)->states('admin')->create();
    }

    /**
     * @param string $name
     * @return string|null
     */
    protected function storedOption($name)
    {
        return DB::table('options')->where('name', $name)->value('value');
    }

    public function test_form_shows_the_current_values()
    {
        $this->actingAs($this->admin)
            ->get('/dashboard/options')
            ->assertSee('value="BIS"')
            ->assertSee('value="Architecture Web Application"');
    }

    public function test_admin_saves_the_four_options()
    {
        $this->actingAs($this->admin)->patch('/dashboard/options', [
            'site_title' => 'New title',
            'tagline' => 'New tagline',
            'email_address' => 'contact@example.com',
            'membership' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('New title', $this->storedOption('site_title'));
        $this->assertSame('New tagline', $this->storedOption('tagline'));
        $this->assertSame('contact@example.com', $this->storedOption('email_address'));
        $this->assertSame('1', $this->storedOption('membership'));
        $this->assertSame('New title', get_option('site_title'));
    }

    public function test_unchecked_membership_is_stored_as_zero()
    {
        $this->actingAs($this->admin)->patch('/dashboard/options', [
            'site_title' => 'BIS',
            'tagline' => 'T',
            'email_address' => 'contact@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertSame('0', $this->storedOption('membership'));
    }

    public function test_options_that_are_not_on_the_form_keep_their_value()
    {
        // B07
        DB::table('options')->insert(['name' => 'logo', 'value' => 'logo.png']);

        $this->actingAs($this->admin)->patch('/dashboard/options', [
            'site_title' => 'BIS',
            'tagline' => 'T',
            'email_address' => 'contact@example.com',
        ])->assertSessionHasNoErrors();

        $this->assertSame('logo.png', $this->storedOption('logo'));
    }

    public function test_saving_creates_a_missing_option_row()
    {
        // B07: update by name, not by row id
        DB::table('options')->where('name', 'tagline')->delete();

        $this->actingAs($this->admin)->patch('/dashboard/options', [
            'site_title' => 'BIS',
            'tagline' => 'Back again',
            'email_address' => 'contact@example.com',
            'membership' => '1',
        ])->assertSessionHasNoErrors();

        $this->assertSame('Back again', $this->storedOption('tagline'));
        $this->assertSame(4, DB::table('options')->count());
    }

    public function test_validation()
    {
        $this->actingAs($this->admin)
            ->patch('/dashboard/options', ['site_title' => '', 'tagline' => 'T', 'email_address' => 'not-an-email'])
            ->assertSessionHasErrors(['site_title', 'email_address']);

        $this->assertSame('BIS', $this->storedOption('site_title'));
    }
}
