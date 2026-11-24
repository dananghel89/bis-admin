@extends('layouts.admin')

@section('title', 'Contacts')
@section('page', 'contacts')

@section('content')

	<div class="card">
		<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
			<ul class="nav nav-pills">
				<li class="nav-item">
					<a class="nav-link py-1 {{ $unreadOnly ? '' : 'active' }}" href="{{ route('contacts') }}">All</a>
				</li>
				<li class="nav-item">
					<a class="nav-link py-1 {{ $unreadOnly ? 'active' : '' }}" href="{{ route('contacts', ['unread' => 1]) }}">Unread</a>
				</li>
			</ul>
			<span class="small text-muted">Messages sent from the contact form on the website</span>
		</div>

		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th scope="col" style="width: 28%">From</th>
						<th scope="col">Subject</th>
						<th scope="col" class="text-end" style="width: 150px">Received</th>
					</tr>
				</thead>

				<tbody>
					@forelse ( $contacts as $contact )
						<tr class="{{ $contact->read_at ? '' : 'fw-bold' }}">
							<td>
								@unless ( $contact->read_at )
									<span class="badge bg-primary me-1">New</span>
								@endunless
								{{ $contact->name }}
								<div class="small text-muted fw-normal">{{ $contact->email }}</div>
							</td>
							<td>
								<a href="{{ route('contactView', $contact) }}" class="text-decoration-none {{ $contact->read_at ? 'text-body' : '' }}">{{ $contact->subject }}</a>
								<div class="small text-muted fw-normal text-truncate" style="max-width: 480px">{{ Str::limit($contact->message, 120) }}</div>
							</td>
							<td class="text-end small text-nowrap">{{ $contact->created_at?->format('Y-m-d H:i') }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="3" class="text-center text-muted py-4">No messages.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if ( $contacts->hasPages() )
			<div class="card-footer">
				{{ $contacts->links() }}
			</div>
		@endif
	</div>

@endsection
