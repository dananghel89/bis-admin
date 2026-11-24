<?php

namespace Tests\Feature\Admin;

use App\Models\MailAccount;
use App\Models\User;
use App\Services\Mailbox\MailboxConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Symfony\Component\Mime\Email;
use Tests\Support\FakeMailboxConnector;
use Tests\TestCase;

/**
 * Alerts (the mailbox) against fake IMAP/SMTP servers; tests never connect to real ones.
 */
class AlertsTest extends TestCase
{
    use RefreshDatabase;

    protected FakeMailboxConnector $servers;

    protected User $ana;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servers = new FakeMailboxConnector;
        $this->app->instance(MailboxConnector::class, $this->servers);

        $this->ana = User::factory()->editor()->create(['name' => 'Ana Architect']);
        MailAccount::factory()->for($this->ana)->create(['email' => 'ana@example.test', 'smtp_host' => 'smtp.example.test']);
    }

    public function test_without_mail_settings_alerts_explains_and_links_to_settings()
    {
        $user = User::factory()->editor()->create();

        $this->actingAs($user)->get('/dashboard/alerts')
            ->assertOk()
            ->assertSee('Your mailbox isn\'t set up yet', false)
            ->assertSee(route('settings') . '#mail-settings', false);

        $this->actingAs($user)->get('/dashboard/alerts/write')->assertRedirect(route('alerts'));
        $this->actingAs($user)->postJson('/dashboard/alerts/write', ['to' => 'x@example.test', 'subject' => 'S', 'body' => 'B'])
            ->assertStatus(422)->assertJson(['message' => 'Set up your mail settings first.']);
        $this->assertSame([], $this->servers->connectedWith);
    }

    public function test_a_server_failure_shows_an_error_and_the_rest_of_the_site_works()
    {
        $this->servers->imapFails = 'Connection refused';

        $this->actingAs($this->ana)->get('/dashboard/alerts')
            ->assertOk()
            ->assertSee('Your mailbox didn\'t answer', false)
            ->assertSee('Connection refused');

        $this->actingAs($this->ana)->get('/dashboard')->assertOk();
    }

    public function test_the_inbox_lists_messages_with_unread_ones_marked()
    {
        $this->servers->receive(1, 'Bob <bob@example.test>', 'Site visit', 'Hello', ['\\Seen']);
        $this->servers->receive(2, 'Cat <cat@example.test>', 'New plans', 'Hi');

        $html = $this->actingAs($this->ana)->get('/dashboard/alerts')
            ->assertOk()
            ->assertSee('Site visit')
            ->assertSee('New plans')
            ->assertSee('ana@example.test')
            ->getContent();

        $this->assertSame(1, substr_count($html, '<span class="badge bg-primary me-1">New</span>'));
        $this->assertSame('ana@example.test', $this->servers->connectedWith[0]->email);
    }

    public function test_reading_a_message_marks_it_seen()
    {
        $this->servers->receive(7, 'Bob <bob@example.test>', 'Site visit', "Line one\n<b>not bold</b>");

        $this->actingAs($this->ana)->get('/dashboard/alerts/message/inbox/7')
            ->assertOk()
            ->assertSee('Site visit')
            ->assertSee('Bob &lt;bob@example.test&gt;', false)
            ->assertSee('&lt;b&gt;not bold&lt;/b&gt;', false)
            ->assertSee(route('alertWrite', ['reply' => 7]), false);

        $this->assertContains('\\Seen', $this->servers->inbox->getMessages()[0]->flags());
    }

    public function test_html_email_is_shown_in_a_sandbox_without_remote_images()
    {
        $this->servers->receive(3, 'Shop <shop@example.test>', 'Offer', '', [], '<p>Hi</p><script>alert(1)</script><img src="https://tracker.example/pixel.gif">');

        $html = $this->actingAs($this->ana)->get('/dashboard/alerts/message/inbox/3')->assertOk()->getContent();

        $this->assertStringContainsString('sandbox="allow-popups allow-popups-to-escape-sandbox"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html); // only escaped, inside srcdoc
        $this->assertStringContainsString('img-src data: cid:&quot;', $html);
        $this->assertStringContainsString('Show images', $html);

        $withImages = $this->actingAs($this->ana)->get('/dashboard/alerts/message/inbox/3?images=1')->getContent();
        $this->assertStringContainsString('img-src data: cid: https: http:', $withImages);
    }

    public function test_a_missing_message_is_a_404()
    {
        $this->actingAs($this->ana)->get('/dashboard/alerts/message/inbox/99')->assertNotFound();
    }

    public function test_sending_an_email_and_keeping_a_copy_in_sent()
    {
        $this->actingAs($this->ana)
            ->postJson('/dashboard/alerts/write', ['to' => 'bob@example.test; cat@example.test', 'cc' => 'dan@example.test', 'subject' => 'Visit', 'body' => 'See you at 10.'])
            ->assertOk()
            ->assertExactJson(['message' => 'Email sent.', 'redirect' => route('alerts', ['box' => 'sent'])]);

        $this->assertCount(1, $this->servers->sentMessages);
        /** @var Email $email */
        $email = $this->servers->sentMessages[0];
        $this->assertSame('ana@example.test', $email->getFrom()[0]->getAddress());
        $this->assertSame('Ana Architect', $email->getFrom()[0]->getName());
        $this->assertSame(['bob@example.test', 'cat@example.test'], array_map(fn ($a) => $a->getAddress(), $email->getTo()));
        $this->assertSame('dan@example.test', $email->getCc()[0]->getAddress());
        $this->assertSame('See you at 10.', $email->getTextBody());

        $copies = $this->servers->sent->getMessages();
        $this->assertCount(1, $copies);
        $this->assertContains('\\Seen', $copies[0]->flags());
        $this->assertSame('Visit', $copies[0]->subject());

        $this->actingAs($this->ana)->get('/dashboard/alerts?box=sent')->assertSee('Visit')->assertSee('bob@example.test');
    }

    public function test_no_copy_is_appended_for_servers_that_save_sent_mail_themselves()
    {
        $this->ana->mailAccount->update(['smtp_host' => 'smtp.gmail.com']);

        $this->actingAs($this->ana)->postJson('/dashboard/alerts/write', ['to' => 'bob@example.test', 'subject' => 'S', 'body' => 'B'])->assertOk();

        $this->assertCount(1, $this->servers->sentMessages);
        $this->assertCount(0, $this->servers->sent->getMessages());
    }

    public function test_a_reply_quotes_the_message_and_threads_it()
    {
        $this->servers->receive(5, 'Bob <bob@example.test>', 'Site visit', "Can we meet?\nThanks", [], null, 'orig-123@example.test');

        $this->actingAs($this->ana)->get('/dashboard/alerts/write?reply=5')
            ->assertOk()
            ->assertSee('value="bob@example.test"', false)
            ->assertSee('value="Re: Site visit"', false)
            ->assertSee('&gt; Can we meet?', false)
            ->assertSee('name="reply_to" value="5"', false);

        $this->actingAs($this->ana)->postJson('/dashboard/alerts/write', ['to' => 'bob@example.test', 'subject' => 'Re: Site visit', 'body' => 'Yes', 'reply_to' => 5])->assertOk();

        $headers = $this->servers->sentMessages[0]->getHeaders();
        $this->assertSame('<orig-123@example.test>', $headers->get('In-Reply-To')->getBodyAsString());
        $this->assertSame('<orig-123@example.test>', $headers->get('References')->getBodyAsString());
    }

    public function test_sending_validation()
    {
        $this->actingAs($this->ana)
            ->postJson('/dashboard/alerts/write', ['to' => 'bob@example.test, not-an-address', 'subject' => '', 'body' => ''])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['to' => '"not-an-address" is not a valid email address.', 'subject', 'body']);

        $this->assertSame([], $this->servers->sentMessages);
    }

    public function test_an_smtp_failure_is_reported_to_the_user()
    {
        $this->servers->smtpFails = 'Authentication failed';

        $this->actingAs($this->ana)
            ->postJson('/dashboard/alerts/write', ['to' => 'bob@example.test', 'subject' => 'S', 'body' => 'B'])
            ->assertStatus(422)
            ->assertJson(['message' => 'The email could not be sent. Check your SMTP settings, then try again.']);
    }
}
