<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Messages from the landing page contact form (staff only, `readContacts`).
 */
class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $unreadOnly = $request->boolean('unread');

        $contacts = Contact::query()
            ->when($unreadOnly, fn ($query) => $query->whereNull('read_at'))
            ->latest()
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.contactsIndex', compact('contacts', 'unreadOnly'));
    }

    /**
     * Opening a message marks it read.
     */
    public function contactView(Contact $contact): View
    {
        if (! $contact->read_at) {
            $contact->forceFill(['read_at' => now()])->save();
        }

        return view('admin.contacts.contactView', compact('contact'));
    }

    public function contactDelete(Request $request, Contact $contact): RedirectResponse|JsonResponse
    {
        $contact->delete();

        return $this->done($request, route('contacts'), 'Message deleted.');
    }
}
