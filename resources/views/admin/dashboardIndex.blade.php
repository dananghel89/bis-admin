@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page', 'dashboard')

@php
	$workBadges = ['pending' => 'bg-secondary', 'in_progress' => 'bg-warning text-dark', 'done' => 'bg-success'];
	$workCards = ['pending' => 'text-bg-secondary', 'in_progress' => 'text-bg-warning', 'done' => 'text-bg-success'];
@endphp

@section('content')

	<p class="text-muted">Welcome, {{ Auth::user()->name }}.</p>

	@isset ( $stats )
		{{-- Staff --}}
		<div class="row g-4 mb-4">
			@foreach ([
				['Published properties', $stats['published'], 'text-bg-primary', 'fa-building', route('infos', ['status' => 'published'])],
				['Drafts', $stats['drafts'], 'text-bg-secondary', 'fa-pencil-alt', route('infos', ['status' => 'draft'])],
				['Architects', $stats['architects'], 'text-bg-info', 'fa-user-tie', route('users', ['role' => 1])],
				['Unread contacts', $stats['unreadContacts'], 'text-bg-danger', 'fa-at', route('contacts', ['unread' => 1])],
				['Licenses to check', $stats['licensesAttention'], 'text-bg-warning', 'fa-file-alt', route('licenses', ['state' => 'attention'])],
			] as [$label, $value, $color, $icon, $url])
				<div class="col-sm-6 col-xl">
					<a href="{{ $url }}" class="text-decoration-none">
						<div class="card {{ $color }} h-100">
							<div class="card-body d-flex justify-content-between align-items-center">
								<div>
									<h4 class="card-title mb-0">{{ $value }}</h4>
									<p class="card-text small mb-0">{{ $label }}</p>
								</div>
								<i class="fas {{ $icon }} fa-2x opacity-50"></i>
							</div>
						</div>
					</a>
				</div>
			@endforeach
		</div>

		<div class="row g-4">
			<div class="col-xl-6">
				@include('admin.dashboardLicenses')
			</div>

			<div class="col-xl-6">
				<div class="card h-100">
					<div class="card-header"><strong class="card-title">Latest Work Status Changes</strong></div>
					<ul class="list-group list-group-flush">
						@forelse ( $changes as $change )
							<li class="list-group-item d-flex justify-content-between align-items-start">
								<div>
									<a href="{{ route('infoView', $change->property_id) }}" class="fw-semibold text-decoration-none">{{ $change->property?->name }}</a>
									<div class="small text-muted">{{ $change->user?->name ?? 'Deleted user' }} · {{ $change->created_at?->format('Y-m-d H:i') }}</div>
								</div>
								<span class="badge {{ $workBadges[$change->to_status] ?? 'bg-secondary' }}">{{ App\Models\Property::WORK_STATUSES[$change->to_status] ?? $change->to_status }}</span>
							</li>
						@empty
							<li class="list-group-item text-muted">No changes yet.</li>
						@endforelse
					</ul>
				</div>
			</div>
		</div>
	@else
		{{-- Architect --}}
		<div class="row g-4 mb-4">
			@foreach ( App\Models\Property::WORK_STATUSES as $status => $label )
				<div class="col-md-4">
					<a href="{{ route('infos', ['work_status' => $status]) }}" class="text-decoration-none">
						<div class="card {{ $workCards[$status] }}">
							<div class="card-body">
								<h4 class="card-title mb-0">{{ $byStatus[$status]->count() }}</h4>
								<p class="card-text small mb-0">{{ $label }}</p>
							</div>
						</div>
					</a>
				</div>
			@endforeach
		</div>

		<div class="row g-4">
			<div class="col-xl-6">
				<div class="card h-100">
					<div class="card-header"><strong class="card-title">Your Properties</strong></div>
					<ul class="list-group list-group-flush">
						@forelse ( $byStatus->flatten() as $property )
							<li class="list-group-item d-flex justify-content-between align-items-center">
								<span><a href="{{ route('infoView', $property) }}" class="text-decoration-none">{{ $property->name }}</a> <small class="text-muted">· {{ $property->city }}</small></span>
								<span class="badge {{ $workBadges[$property->work_status] ?? 'bg-secondary' }}">{{ $property->workStatusLabel() }}</span>
							</li>
						@empty
							<li class="list-group-item text-muted">No properties yet.</li>
						@endforelse
					</ul>
				</div>
			</div>

			<div class="col-xl-6">
				@include('admin.dashboardLicenses')
			</div>
		</div>
	@endisset

@endsection
