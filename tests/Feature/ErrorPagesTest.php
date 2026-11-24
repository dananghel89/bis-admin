<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_missing_page_uses_the_theme_error_card()
    {
        $this->get('/no-such-page')
            ->assertNotFound()
            ->assertSee('<body class="error-page">', false)
            ->assertSee('Page Not Found')
            ->assertSee('href="' . url('/dashboard') . '"', false);
    }

    public function test_a_forbidden_page_says_access_denied()
    {
        $this->actingAs(User::factory()->editor()->create())
            ->get('/dashboard/users')
            ->assertForbidden()
            ->assertSee('Access Denied');
    }

    public function test_every_error_page_renders()
    {
        foreach (['403' => 'Access Denied', '404' => 'Page Not Found', '419' => 'Page Expired', '500' => 'Something Went Wrong'] as $code => $title) {
            $html = view("errors.{$code}")->render();

            $this->assertStringContainsString("<div class=\"error-number\">{$code}</div>", $html);
            $this->assertStringContainsString($title, $html);
        }
    }
}
