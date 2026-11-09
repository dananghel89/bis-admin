<?php

namespace Tests\Feature;

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
        $this->get('/register')->assertSee('href="' . url('/terms-of-service') . '"', false);
    }

    public function test_bell_footer_credit_is_kept()
    {
        // Required by the BootstrapMade free license.
        $credit = '<a href="https://bootstrapmade.com/">Bootstrap Templates</a> by BootstrapMade';

        $this->get('/')->assertSee($credit, false);
        $this->get('/terms-of-service')->assertSee($credit, false);
    }
}
