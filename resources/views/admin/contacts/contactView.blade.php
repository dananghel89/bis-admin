@extends('layouts.admin')

@section('title', $contact->subject)
@section('page', 'contacts')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('contacts') }}">Contacts</a></li>
	<li class="breadcrumb-item active" aria-current="page">Message</li>
@endsection

@section('content')

	<div class="row">
		<div class="col-xl-8">
			<div class="card">
				<div class="card-header">
					<dl class="row mb-0 small">
						<dt class="col-sm-3">From</dt>
						<dd class="col-sm-9">{{ $contact->name }} &lt;{{ $contact->email }}&gt;</dd>
						<dt class="col-sm-3">Received</dt>
						<dd class="col-sm-9">{{ $contact->created_at?->format('Y-m-d H:i') }}</dd>
						<dt class="col-sm-3">IP address</dt>
						<dd class="col-sm-9 mb-0">{{ $contact->ip ?? '—' }}</dd>
					</dl>
				</div>

				<div class="card-body">
					<div style="white-space: pre-wrap">{{ $contact->message }}</div>
				</div>

				<div class="card-footer d-flex flex-wrap gap-2">
					<a href="mailto:{{ $contact->email }}?subject={{ rawurlencode('Re: '.$contact->subject) }}" class="btn btn-sm btn-primary"><i class="fas fa-reply me-1"></i> Reply by Email</a>

					@can ( 'useMailbox' )
						@if ( Auth::user()->mailAccount )
							<a href="{{ route('alertWrite') }}?{{ http_build_query(['to' => $contact->email, 'subject' => 'Re: '.$contact->subject]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-inbox me-1"></i> Reply from Alerts</a>
						@endif
					@endcan

					<x-form.ajax :action="route('contactDelete', $contact)" method="DELETE" class="d-inline" data-confirm="Delete this message?">
						<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i> Delete</button>
					</x-form.ajax>

					<a href="{{ route('contacts') }}" class="btn btn-sm btn-outline-secondary ms-auto"><i class="fas fa-angle-left me-1"></i> Back to Contacts</a>
				</div>
			</div>
		</div>
	</div>

@endsection
