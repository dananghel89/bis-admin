<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class LandingTest extends TestCase
{
    use RefreshDatabase;

    public function test_front_page_renders_with_the_site_title()
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>BIS - Building Information System | Architecture Web Application</title>', false);
    }

    public function test_terms_of_service_renders()
    {
        $this->get('/terms-of-service')->assertOk()->assertSee('<title>BIS - Terms of Service', false);
        $this->assertSame(url('/terms-of-service'), route('tos'));
    }

    public function test_links_point_to_the_new_url()
    {
        $this->get('/')->assertSee('href="' . url('/terms-of-service') . '"', false);
    }

    public function test_site_title_and_tagline_come_from_the_config()
    {
        config(['project.site_title' => 'Test Site', 'project.tagline' => 'Test Tagline']);

        $this->get('/')
            ->assertSee('<title>Test Site - Building Information System | Test Tagline</title>', false)
            ->assertSee('© ' . date('Y') . ' Test Site', false);
    }

    public function test_hero_button_logs_in_or_opens_the_dashboard()
    {
        $this->get('/')->assertSee('<a class="btn btn-full" href="' . route('login') . '">Log In</a>', false);

        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertSee('<a class="btn btn-full" href="' . route('dashboard') . '">Dashboard</a>', false);
    }

    public function test_there_is_no_public_sign_up()
    {
        $this->get('/register')->assertNotFound();
        $this->post('/register', ['name' => 'X'])->assertNotFound();
        $this->get('/login')->assertDontSee('Sign Up');
        $this->get('/')->assertDontSee('/register');
    }

    public function test_the_options_page_is_gone()
    {
        $this->actingAs(User::factory()->admin()->create())
            ->get('/dashboard/options')
            ->assertNotFound();
    }

    public function test_bell_footer_credit_is_kept()
    {
        // Required by the BootstrapMade free license.
        $credit = '<a href="https://bootstrapmade.com/">Bootstrap Templates</a> by BootstrapMade';

        $this->get('/')->assertSee($credit, false);
        $this->get('/terms-of-service')->assertSee($credit, false);
    }
}
