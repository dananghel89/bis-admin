<?php

namespace Tests\Feature\Admin;

use App\Models\Contact;
use App\Models\License;
use App\Models\Property;
use App\Models\PropertyStatusChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_see_counts_licenses_to_check_and_latest_changes()
    {
        $staff = User::factory()->admin()->create();
        User::factory()->editor()->count(3)->create();
        $published = Property::factory()->published()->count(2)->create();
        Property::factory()->draft()->create();
        Contact::factory()->count(2)->create();
        Contact::factory()->read()->create();
        License::factory()->for($published[0])->expired()->create(['title' => 'Old permit']);
        License::factory()->for($published[0])->create(['title' => 'Fine permit', 'expires_at' => today()->addYears(2)]);
        PropertyStatusChange::factory()->for($published[1])->create(['to_status' => 'done']);

        $html = $this->actingAs($staff)->get('/dashboard')
            ->assertOk()
            ->assertSee('Old permit')
            ->assertDontSee('Fine permit')
            ->assertSee($published[1]->name)
            ->getContent();

        foreach (['Published properties' => 2, 'Drafts' => 1, 'Architects' => 3, 'Unread contacts' => 2, 'Licenses to check' => 1] as $label => $count) {
            $this->assertMatchesRegularExpression('#<h4 class="card-title mb-0">' . $count . '</h4>\s*<p class="card-text small mb-0">' . $label . '</p>#', $html, $label);
        }

        // The header bell adds them up
        $this->assertStringContainsString('2 unread contact messages', $html);
        $this->assertStringContainsString('1 license expired or expiring', $html);
    }

    public function test_architects_see_their_properties_by_work_status()
    {
        $ana = User::factory()->editor()->create();
        $ben = User::factory()->editor()->create();
        $mine = Property::factory()->published()->create(['name' => 'Ana House', 'work_status' => 'in_progress']);
        $mine->architects()->attach($ana);
        Property::factory()->published()->create(['name' => 'Open House', 'work_status' => 'pending']);
        Property::factory()->published()->create(['name' => 'Ben House'])->architects()->attach($ben);
        Property::factory()->draft()->create(['name' => 'Draft House']);
        License::factory()->for($mine)->expiringSoon()->create(['title' => 'Ana permit']);

        $html = $this->actingAs($ana)->get('/dashboard')
            ->assertOk()
            ->assertSee('Ana House')
            ->assertSee('Open House')
            ->assertDontSee('Ben House')
            ->assertDontSee('Draft House')
            ->assertSee('Ana permit')
            ->assertDontSee('Unread contacts')
            ->assertDontSee('aria-label="Notifications"', false)
            ->getContent();

        $this->assertMatchesRegularExpression('#<h4 class="card-title mb-0">1</h4>\s*<p class="card-text small mb-0">In progress</p>#', $html);
        $this->assertMatchesRegularExpression('#<h4 class="card-title mb-0">0</h4>\s*<p class="card-text small mb-0">Done</p>#', $html);
    }
}
