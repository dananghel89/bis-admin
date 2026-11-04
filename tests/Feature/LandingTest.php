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
            ->assertSee('<title>BIS - Building Information System | Architecture Web Application</title>');
    }

    public function test_terms_of_service_renders()
    {
        $this->get('/tos')->assertOk()->assertSee('<title>BIS - Terms of Service');
    }

    public function test_bell_footer_credit_is_kept()
    {
        // Required by the BootstrapMade free license, see docs/theme-licensing-plan.md.
        $credit = '<a href="https://bootstrapmade.com/">Bootstrap Templates</a> by BootstrapMade';

        $this->get('/')->assertSee($credit);
        $this->get('/tos')->assertSee($credit);
    }
}
