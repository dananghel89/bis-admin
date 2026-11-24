{{-- Plain text email: unescaped output is safe here and keeps names like O'Brien readable. --}}
New message from the {!! config('project.site_title') !!} contact form.

Name: {!! $contact->name !!}
Email: {!! $contact->email !!}
Subject: {!! $contact->subject !!}
Sent: {{ $contact->created_at }}

{!! $contact->message !!}
