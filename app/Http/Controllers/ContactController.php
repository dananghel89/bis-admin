<?php

namespace App\Http\Controllers;

use App\Http\Requests\ContactRequest;
use App\Mail\ContactReceived;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    /**
     * Save a landing page contact message and tell staff about it.
     */
    public function store(ContactRequest $request): RedirectResponse
    {
        $done = redirect()->to(route('front').'#contact')
            ->with('contact_status', 'Thank you! Your message has been sent.');

        // Bots fill in every field, people never see this one: act as if it worked.
        if ($request->filled('website')) {
            return $done;
        }

        $contact = Contact::create($request->validated() + ['ip' => $request->ip()]);

        // The message is saved either way, so a mail problem only gets logged.
        try {
            Mail::to(config('project.contact_email'))->send(new ContactReceived($contact));
        } catch (Throwable $e) {
            report($e);
        }

        return $done;
    }
}
