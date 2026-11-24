@extends('layouts.admin')

@section('title', $user->name)
@section('page', 'users')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('users') }}">Users</a></li>
	<li class="breadcrumb-item active" aria-current="page">{{ $user->name }}</li>
@endsection

@section('content')

	<div class="row g-4">
		<div class="col-xl-6">
			<div class="card h-100">
				<div class="card-header d-flex justify-content-between align-items-center">
					<strong class="card-title">Account</strong>
					<span class="badge {{ $user->isStaff() ? 'bg-primary' : 'bg-info text-dark' }}">{{ $user->roleLabel() }}</span>
				</div>

				<div class="card-body">
					<div class="d-flex align-items-center mb-3">
						<img src="{{ get_gravatar($user->email, 128) }}" alt="" class="rounded-circle me-3" width="64" height="64">
						<div>
							<div class="fs-5 fw-semibold">{{ $user->name }}</div>
							<div class="text-muted">{{ $user->email }}</div>
						</div>
					</div>

					<dl class="row mb-0">
						<dt class="col-sm-4">Created</dt>
						<dd class="col-sm-8">{{ $user->created_at?->format('Y-m-d H:i') }}</dd>
						<dt class="col-sm-4">Updated</dt>
						<dd class="col-sm-8 mb-0">{{ $user->updated_at?->format('Y-m-d H:i') }}</dd>
					</dl>
				</div>

				<div class="card-footer d-flex flex-wrap gap-2">
					<a href="{{ route('userEdit', $user) }}" class="btn btn-sm btn-primary"><i class="fas fa-pen me-1"></i> Edit</a>

					@unless ( $user->is(Auth::user()) )
						<x-form.ajax :action="route('userDelete', $user)" method="DELETE" class="d-inline" data-confirm="Delete the account of {{ $user->name }}? This can't be undone.">
							<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i> Delete</button>
						</x-form.ajax>
					@endunless

					<a href="{{ route('users') }}" class="btn btn-sm btn-outline-secondary ms-auto"><i class="fas fa-angle-left me-1"></i> Back to Users</a>
				</div>
			</div>
		</div>

		@if ( $user->isArchitect() )
			<div class="col-xl-6">
				<div class="card h-100">
					<div class="card-header">
						<strong class="card-title">Assigned Properties</strong>
					</div>

					<ul class="list-group list-group-flush">
						@forelse ( $user->properties as $property )
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span><a href="{{ route('infoView', $property) }}" class="text-decoration-none">{{ $property->name }}</a> <small class="text-muted">· {{ $property->city }}</small></span>
								<span class="badge bg-secondary">{{ $property->workStatusLabel() }}</span>
							</li>
						@empty
							<li class="list-group-item text-muted">None. This architect still sees every published property with no architect assigned.</li>
						@endforelse
					</ul>
				</div>
			</div>
		@endif
	</div>

@endsection
