<?php

namespace Tests\Feature;

use App\Mail\ContactReceived;
use App\Models\Contact;
use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use RuntimeException;
use Tests\TestCase;

class ContactFormTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A valid contact form.
     *
     * @return array<string, string>
     */
    protected function validData(array $overrides = []): array
    {
        return array_merge([
            'name' => "Anna O'Brien",
            'email' => 'anna@example.com',
            'subject' => 'Access for my office',
            'message' => "Hello,\nwe need two accounts.",
        ], $overrides);
    }

    public function test_form_posts_to_the_contact_route_with_a_csrf_token()
    {
        $this->get('/')
            ->assertSee('action="' . route('contact') . '"', false)
            ->assertSee('name="_token"', false)
            ->assertSee('name="website"', false)
            ->assertDontSee('<fieldset disabled>', false);
    }

    public function test_a_message_is_saved_and_staff_are_emailed()
    {
        Mail::fake();
        config(['project.contact_email' => 'staff@example.test']);

        $this->post('/contact', $this->validData())
            ->assertRedirect(route('front') . '#contact')
            ->assertSessionHas('contact_status');

        $contact = Contact::sole();
        $this->assertSame("Anna O'Brien", $contact->name);
        $this->assertSame('anna@example.com', $contact->email);
        $this->assertSame("Hello,\nwe need two accounts.", $contact->message);
        $this->assertSame('127.0.0.1', $contact->ip);
        $this->assertNull($contact->read_at);

        Mail::assertSent(ContactReceived::class, function (ContactReceived $mail) use ($contact) {
            return $mail->hasTo('staff@example.test')
                && $mail->hasReplyTo('anna@example.com')
                && $mail->contact->is($contact);
        });
    }

    public function test_the_email_is_plain_text_with_the_message()
    {
        $contact = Contact::factory()->create(['name' => "Anna O'Brien", 'message' => 'Line <b>one</b>']);

        $text = (new ContactReceived($contact))->render();

        $this->assertStringContainsString("Name: Anna O'Brien", $text);
        $this->assertStringContainsString('Line <b>one</b>', $text);
        $this->assertSame('Contact form: ' . $contact->subject, (new ContactReceived($contact))->envelope()->subject);
    }

    public function test_the_success_message_is_shown()
    {
        $this->post('/contact', $this->validData());

        $this->get('/')->assertSee('<div id="sendmessage" class="show">Thank you! Your message has been sent.</div>', false);
    }

    public function test_invalid_input_is_rejected_and_kept()
    {
        Mail::fake();

        $this->post('/contact', $this->validData(['email' => 'not-an-email', 'message' => '']))
            ->assertRedirect(route('front') . '#contact')
            ->assertSessionHasErrors(['email', 'message'])
            ->assertSessionHasInput('name', "Anna O'Brien");

        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_fields_are_required_and_limited()
    {
        $this->post('/contact', [])->assertSessionHasErrors(['name', 'email', 'subject', 'message']);

        $this->post('/contact', $this->validData(['subject' => str_repeat('x', 151), 'message' => str_repeat('x', 5001)]))
            ->assertSessionHasErrors(['subject', 'message']);

        $this->assertSame(0, Contact::count());
    }

    public function test_validation_errors_are_shown_under_the_fields()
    {
        $this->post('/contact', $this->validData(['email' => 'not-an-email']));

        $this->get('/')
            ->assertSee('<div class="validation show">', false)
            ->assertSee('value="Anna O&#039;Brien"', false);
    }

    public function test_the_honeypot_saves_nothing_but_looks_like_success()
    {
        Mail::fake();

        $this->post('/contact', $this->validData(['website' => 'http://spam.example']))
            ->assertRedirect(route('front') . '#contact')
            ->assertSessionHas('contact_status');

        $this->assertSame(0, Contact::count());
        Mail::assertNothingSent();
    }

    public function test_a_mail_failure_still_saves_the_message()
    {
        Mail::shouldReceive('to')->andThrow(new RuntimeException('SMTP is down'));
        Log::spy();

        $this->post('/contact', $this->validData())->assertSessionHas('contact_status');

        $this->assertSame(1, Contact::count());
        Log::shouldHaveReceived('error')->once();
    }

    public function test_messages_are_rate_limited()
    {
        Mail::fake();

        // At most 3 messages per 10 minutes from one IP
        for ($i = 0; $i < 3; $i++) {
            $this->post('/contact', $this->validData())->assertRedirect(route('front') . '#contact');
        }

        $this->post('/contact', $this->validData())
            ->assertRedirect(route('front') . '#contact')
            ->assertSessionHasErrors('contact');

        $this->assertSame(3, Contact::count());
    }

    public function test_the_rate_limit_message_is_shown_on_the_form()
    {
        Mail::fake();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/contact', $this->validData());
        }

        // Follow the redirect like a browser does
        $this->followingRedirects()
            ->post('/contact', $this->validData())
            ->assertOk()
            ->assertSee('<div id="errormessage" class="show">Too many messages. Please try again in a few minutes.</div>', false)
            ->assertSee('value="Anna O&#039;Brien"', false);
    }

    public function test_the_contact_limit_is_separate_from_password_resets()
    {
        Mail::fake();
        Notification::fake();
        $user = User::factory()->create();

        for ($i = 0; $i < 3; $i++) {
            $this->post('/contact', $this->validData());
        }

        $this->post('/password/email', ['email' => $user->email])->assertSessionHas('status');
        Notification::assertSentTo($user, ResetPassword::class);
    }
}
