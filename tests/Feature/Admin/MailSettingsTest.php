<?php

namespace Tests\Feature\Admin;

use App\Models\MailAccount;
use App\Models\User;
use App\Services\Mailbox\MailboxConnector;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\FakeMailboxConnector;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected FakeMailboxConnector $servers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->servers = new FakeMailboxConnector;
        $this->app->instance(MailboxConnector::class, $this->servers);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'mail_email' => 'ana@example.test',
            'mail_username' => '',
            'mail_password' => 'mail-pass',
            'imap_host' => 'imap.example.test',
            'imap_port' => 993,
            'imap_encryption' => 'ssl',
            'smtp_host' => 'smtp.example.test',
            'smtp_port' => 587,
            'smtp_encryption' => 'starttls',
        ], $overrides);
    }

    public function test_users_save_their_own_mail_settings()
    {
        $ana = User::factory()->editor()->create();

        $this->actingAs($ana)->get('/dashboard/settings')->assertSee('Mail Settings for Alerts')->assertSee('Not set up');

        $this->actingAs($ana)->patchJson('/dashboard/settings/mail', $this->validData())
            ->assertOk()
            ->assertJson(['message' => 'Mail settings saved.', 'redirect' => route('settings')]);

        $account = $ana->fresh()->mailAccount;
        $this->assertSame('ana@example.test', $account->email);
        $this->assertSame('mail-pass', $account->password);
        $this->assertSame(587, $account->smtp_port);
        $this->assertSame('starttls', $account->smtp_encryption);

        // The password is never sent back to the page
        $this->actingAs($ana->fresh())->get('/dashboard/settings')->assertSee('<span class="badge bg-success">Set up</span>', false)->assertDontSee('mail-pass');
    }

    public function test_an_empty_password_keeps_the_saved_one()
    {
        $ana = User::factory()->editor()->create();
        MailAccount::factory()->for($ana)->create(['password' => 'old-pass']);

        $this->actingAs($ana)->patchJson('/dashboard/settings/mail', $this->validData(['mail_password' => '', 'imap_host' => 'mail.example.test']))->assertOk();

        $account = $ana->fresh()->mailAccount;
        $this->assertSame('old-pass', $account->password);
        $this->assertSame('mail.example.test', $account->imap_host);
        $this->assertSame(1, MailAccount::count());
    }

    public function test_validation()
    {
        $ana = User::factory()->editor()->create();

        $this->actingAs($ana)
            ->patchJson('/dashboard/settings/mail', $this->validData(['mail_email' => 'bad', 'mail_password' => '', 'imap_port' => 70000, 'smtp_encryption' => 'magic', 'smtp_host' => '']))
            ->assertStatus(422)
            ->assertJsonValidationErrors(['mail_email', 'mail_password', 'imap_port', 'smtp_encryption', 'smtp_host']);

        $this->assertSame(0, MailAccount::count());
    }

    public function test_the_connection_test_uses_the_typed_settings_without_saving()
    {
        $ana = User::factory()->editor()->create();

        $this->actingAs($ana)->postJson('/dashboard/settings/mail/test', $this->validData())
            ->assertOk()
            ->assertJson(['message' => 'Both servers answered: reading (IMAP) and sending (SMTP) work.']);

        $this->assertSame('imap.example.test', $this->servers->connectedWith[0]->imap_host);
        $this->assertSame(0, MailAccount::count());

        $this->servers->imapFails = 'Login failed';
        $this->actingAs($ana)->patchJson('/dashboard/settings/mail/test', $this->validData())
            ->assertStatus(422)
            ->assertJson(['message' => 'IMAP failed: Login failed SMTP works.']);
    }

    public function test_the_test_with_an_empty_password_uses_the_saved_one()
    {
        $ana = User::factory()->editor()->create();
        MailAccount::factory()->for($ana)->create(['password' => 'saved-pass']);

        $this->actingAs($ana)->postJson('/dashboard/settings/mail/test', $this->validData(['mail_password' => '']))->assertOk();

        $this->assertSame('saved-pass', $this->servers->connectedWith[0]->password);
    }

    public function test_users_remove_their_mail_settings()
    {
        $ana = User::factory()->editor()->create();
        MailAccount::factory()->for($ana)->create();

        $this->actingAs($ana)->deleteJson('/dashboard/settings/mail')->assertOk();

        $this->assertSame(0, MailAccount::count());
    }

    public function test_staff_edit_someone_elses_mail_settings_but_architects_cant()
    {
        $staff = User::factory()->admin()->create();
        $ana = User::factory()->editor()->create();
        $ben = User::factory()->editor()->create();

        $this->actingAs($staff)->get("/dashboard/users/edit-user/{$ana->id}")->assertSee('Mail Settings for Alerts');

        $this->actingAs($staff)->patchJson("/dashboard/users/mail-settings/{$ana->id}", $this->validData())
            ->assertOk()
            ->assertJson(['redirect' => route('userEdit', $ana)]);
        $this->assertSame('ana@example.test', $ana->fresh()->mailAccount->email);
        $this->assertNull($staff->fresh()->mailAccount);

        $this->actingAs($ben)->patch("/dashboard/users/mail-settings/{$ana->id}", $this->validData())->assertForbidden();
        $this->actingAs($ben)->delete("/dashboard/users/mail-settings/{$ana->id}")->assertForbidden();
        $this->assertNotNull($ana->fresh()->mailAccount);
    }
}
