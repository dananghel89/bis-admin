<?php

namespace Tests\Feature\Admin;

use App\Models\Contact;
use App\Models\MailAccount;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactsTest extends TestCase
{
    use RefreshDatabase;

    protected User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->staff = User::factory()->admin()->create();
    }

    public function test_only_staff_see_contact_messages()
    {
        $architect = User::factory()->editor()->create();
        $contact = Contact::factory()->create();

        $this->actingAs($architect)->get('/dashboard/contacts')->assertForbidden();
        $this->actingAs($architect)->get("/dashboard/contacts/view-contact/{$contact->id}")->assertForbidden();
        $this->actingAs($architect)->delete("/dashboard/contacts/view-contact/{$contact->id}")->assertForbidden();
        $this->actingAs($architect)->get('/dashboard')->assertDontSee(route('contacts'), false);

        $this->actingAs($this->staff)->get('/dashboard/contacts')->assertOk();
        $this->assertNull($contact->fresh()->read_at);
    }

    public function test_the_list_is_newest_first_with_unread_marked_and_counted_in_the_menu()
    {
        Contact::factory()->read()->create(['subject' => 'Old question', 'created_at' => now()->subDay()]);
        Contact::factory()->create(['subject' => 'New question']);
        Contact::factory()->create(['subject' => 'Another new one']);

        $html = $this->actingAs($this->staff)->get('/dashboard/contacts')
            ->assertSeeInOrder(['New question', 'Old question'])
            ->getContent();

        $this->assertSame(2, substr_count($html, '<span class="badge bg-primary me-1">New</span>'));
        $this->assertStringContainsString('<span class="badge rounded-pill bg-danger ms-auto nav-text" title="Unread">2</span>', $html);

        $this->actingAs($this->staff)->get('/dashboard/contacts?unread=1')
            ->assertSee('New question')->assertDontSee('Old question');
    }

    public function test_opening_a_message_marks_it_read()
    {
        $contact = Contact::factory()->create(['name' => 'Anna', 'email' => 'anna@example.com', 'subject' => 'Access', 'message' => "Hello\n<b>team</b>"]);

        $this->actingAs($this->staff)->get("/dashboard/contacts/view-contact/{$contact->id}")
            ->assertOk()
            ->assertSee('Anna &lt;anna@example.com&gt;', false)
            ->assertSee('&lt;b&gt;team&lt;/b&gt;', false)
            ->assertSee('mailto:anna@example.com?subject=Re%3A%20Access', false);

        $this->assertNotNull($contact->fresh()->read_at);
    }

    public function test_reply_from_alerts_starts_an_email()
    {
        MailAccount::factory()->for($this->staff)->create();
        $contact = Contact::factory()->create(['email' => 'anna@example.com', 'subject' => 'Access']);

        $link = route('alertWrite') . '?' . http_build_query(['to' => 'anna@example.com', 'subject' => 'Re: Access']);

        $this->actingAs($this->staff->fresh())->get("/dashboard/contacts/view-contact/{$contact->id}")
            ->assertSee(e($link), false);

        $this->actingAs($this->staff->fresh())->get($link)
            ->assertSee('value="anna@example.com"', false)
            ->assertSee('value="Re: Access"', false);
    }

    public function test_staff_delete_a_message()
    {
        $contact = Contact::factory()->create();

        $this->actingAs($this->staff)->deleteJson("/dashboard/contacts/view-contact/{$contact->id}")
            ->assertOk()
            ->assertJson(['redirect' => route('contacts')]);

        $this->assertSame(0, Contact::count());
    }
}
