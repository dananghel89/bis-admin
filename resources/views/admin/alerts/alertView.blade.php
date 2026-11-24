@extends('layouts.admin')

@section('title', $message->subject() ?: '(no subject)')
@section('page', 'alerts')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('alerts', ['box' => $box]) }}">Alerts</a></li>
	<li class="breadcrumb-item active" aria-current="page">{{ App\Services\Mailbox\UserMailbox::BOXES[$box] }}</li>
@endsection

@php
	$address = fn ($a) => $a ? ($a->name() ? $a->name().' <'.$a->email().'>' : $a->email()) : '';

	// Email HTML is untrusted: it is shown in a sandboxed frame (no scripts, no access to
	// this page) with a policy that blocks remote images and other remote content.
	$document = null;
	if ( $html ) {
		$images = $showImages ? ' https: http:' : '';
		$document = '<!DOCTYPE html><html><head><meta charset="utf-8">'
			.'<meta http-equiv="Content-Security-Policy" content="default-src \'none\'; style-src \'unsafe-inline\'; font-src data:; img-src data: cid:'.$images.'">'
			.'<base target="_blank"><style>body{font-family:system-ui,sans-serif;font-size:14px;margin:12px;color:#212529;background:#fff;word-wrap:break-word}</style>'
			.'</head><body>'.$html.'</body></html>';
	}
@endphp

@section('content')

	<div class="card">
		<div class="card-header">
			<dl class="row mb-0 small">
				<dt class="col-sm-2">From</dt>
				<dd class="col-sm-10">{{ $address($message->from()) }}</dd>
				<dt class="col-sm-2">To</dt>
				<dd class="col-sm-10">{{ collect($message->to())->map($address)->implode(', ') }}</dd>
				@if ( $message->cc() )
					<dt class="col-sm-2">Cc</dt>
					<dd class="col-sm-10">{{ collect($message->cc())->map($address)->implode(', ') }}</dd>
				@endif
				<dt class="col-sm-2">Date</dt>
				<dd class="col-sm-10 mb-0">{{ $message->date()?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</dd>
			</dl>
		</div>

		<div class="card-body">
			@if ( $document )
				@unless ( $showImages )
					<div class="alert alert-light border small py-2">
						Remote images are hidden to protect your privacy.
						<a href="{{ route('alertView', ['box' => $box, 'uid' => $message->uid(), 'images' => 1]) }}">Show images</a>
					</div>
				@endunless

				<iframe class="w-100 border rounded bg-white" style="height: 520px" title="Message"
					sandbox="allow-popups allow-popups-to-escape-sandbox"
					srcdoc="{{ $document }}"></iframe>
			@else
				<div style="white-space: pre-wrap" class="font-monospace small">{{ $message->text() }}</div>
			@endif

			@if ( $message->hasAttachments() )
				<p class="small text-muted mt-3 mb-0"><i class="fas fa-paperclip me-1"></i> This message has attachments; open it in your email program to see them.</p>
			@endif
		</div>

		<div class="card-footer d-flex gap-2">
			@if ( $box === 'inbox' )
				<a href="{{ route('alertWrite', ['reply' => $message->uid()]) }}" class="btn btn-sm btn-primary"><i class="fas fa-reply me-1"></i> Reply</a>
			@endif
			<a href="{{ route('alerts', ['box' => $box]) }}" class="btn btn-sm btn-outline-secondary ms-auto"><i class="fas fa-angle-left me-1"></i> Back</a>
		</div>
	</div>

@endsection
