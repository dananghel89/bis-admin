@extends('layouts.admin')

@section('title', 'Alerts')
@section('page', 'alerts')

@section('content')

	@if ( ! $mailbox )
		<div class="alert alert-warning" role="alert">
			<h5 class="alert-heading"><i class="fas fa-envelope me-1"></i> Your mailbox isn't set up yet</h5>
			<p class="mb-2">Alerts shows the email of your own account. Add its IMAP and SMTP settings first.</p>
			<a href="{{ route('settings') }}#mail-settings" class="btn btn-sm btn-warning">Open Mail Settings</a>
		</div>
	@elseif ( $error )
		<div class="alert alert-danger" role="alert">
			<h5 class="alert-heading"><i class="fas fa-exclamation-triangle me-1"></i> Your mailbox didn't answer</h5>
			<p class="mb-2">{{ $error }}</p>
			<a href="{{ route('settings') }}#mail-settings" class="btn btn-sm btn-outline-danger">Check Mail Settings</a>
			<a href="{{ route('alerts', ['box' => $box]) }}" class="btn btn-sm btn-outline-secondary">Try Again</a>
		</div>
	@else
		<div class="card">
			<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
				<ul class="nav nav-pills">
					@foreach ( App\Services\Mailbox\UserMailbox::BOXES as $key => $label )
						<li class="nav-item">
							<a class="nav-link py-1 {{ $box === $key ? 'active' : '' }}" href="{{ route('alerts', ['box' => $key]) }}">
								<i class="fas {{ $key === 'inbox' ? 'fa-inbox' : 'fa-paper-plane' }} me-1"></i> {{ $label }}
							</a>
						</li>
					@endforeach
				</ul>

				<div class="d-flex align-items-center gap-2">
					<span class="small text-muted d-none d-md-inline">{{ $mailbox->account()->email }}</span>
					<a href="{{ route('alertWrite') }}" class="btn btn-sm btn-success"><i class="fas fa-pen me-1"></i> Write</a>
				</div>
			</div>

			<div class="table-responsive">
				<table class="table table-hover align-middle mb-0">
					<thead>
						<tr>
							<th scope="col" style="width: 30%">{{ $box === 'sent' ? 'To' : 'From' }}</th>
							<th scope="col">Subject</th>
							<th scope="col" class="text-end" style="width: 140px">Date</th>
							<th scope="col" style="width: 1%"><span class="visually-hidden">Actions</span></th>
						</tr>
					</thead>

					<tbody>
						@forelse ( $messages as $message )
							@php
								$unread = $box === 'inbox' && ! $message->isSeen();
								$who = $box === 'sent'
									? collect($message->to())->map(fn ($a) => $a->name() ?: $a->email())->implode(', ')
									: ($message->from()?->name() ?: $message->from()?->email());
							@endphp
							<tr class="{{ $unread ? 'fw-bold' : '' }}">
								<td class="text-truncate" style="max-width: 260px">
									@if ( $unread )
										<span class="badge bg-primary me-1">New</span>
									@endif
									{{ $who ?: '(unknown)' }}
								</td>
								<td>
									<a href="{{ route('alertView', ['box' => $box, 'uid' => $message->uid()]) }}" class="text-decoration-none">{{ $message->subject() ?: '(no subject)' }}</a>
								</td>
								<td class="text-end small text-nowrap">{{ $message->date()?->timezone(config('app.timezone'))->format('Y-m-d H:i') }}</td>
								<td class="text-end">
									<x-form.ajax :action="route('alertDelete', ['box' => $box, 'uid' => $message->uid()])" method="DELETE" class="d-inline" data-confirm="Delete this email?">
										<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
									</x-form.ajax>
								</td>
							</tr>
						@empty
							<tr>
								<td colspan="4" class="text-center text-muted py-4">No messages.</td>
							</tr>
						@endforelse
					</tbody>
				</table>
			</div>

			@if ( $messages->hasPages() )
				<div class="card-footer">
					{{ $messages->links() }}
				</div>
			@endif
		</div>
	@endif

@endsection
