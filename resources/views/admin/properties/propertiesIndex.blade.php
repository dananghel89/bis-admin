@extends('layouts.admin')

@section('title', 'Infos')
@section('page', 'infos')

@php
	$workBadges = ['pending' => 'bg-secondary', 'in_progress' => 'bg-warning text-dark', 'done' => 'bg-success'];
@endphp

@section('content')

	<div class="card">
		<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
			<form method="GET" action="{{ route('infos') }}" class="d-flex flex-wrap gap-2" role="search">
				<input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Name, address or city" aria-label="Search properties" style="max-width: 240px">

				<select name="work_status" class="form-select form-select-sm" aria-label="Work status" style="max-width: 170px">
					<option value="">Any work status</option>
					@foreach ( App\Models\Property::WORK_STATUSES as $value => $label )
						<option value="{{ $value }}" @selected($workStatus === $value)>{{ $label }}</option>
					@endforeach
				</select>

				@can ( 'manageProperties' )
					<select name="status" class="form-select form-select-sm" aria-label="Status" style="max-width: 150px">
						<option value="">Draft or published</option>
						@foreach ( App\Models\Property::STATUSES as $value => $label )
							<option value="{{ $value }}" @selected($status === $value)>{{ $label }}</option>
						@endforeach
					</select>
				@endcan

				<button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search me-1"></i> Filter</button>
			</form>

			@can ( 'manageProperties' )
				<a href="{{ route('infoAdd') }}" class="btn btn-sm btn-success">
					<i class="fas fa-plus me-1"></i> Add Property
				</a>
			@endcan
		</div>

		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th scope="col">Property</th>
						<th scope="col">City</th>
						<th scope="col">Work Status</th>
						@can ( 'manageProperties' )
							<th scope="col">Status</th>
							<th scope="col">Architects</th>
						@endcan
						<th scope="col">Plans</th>
						<th scope="col">Licenses</th>
						<th scope="col" class="text-end">Actions</th>
					</tr>
				</thead>

				<tbody>
					@forelse ( $properties as $property )
						<tr>
							<td>
								<a href="{{ route('infoView', $property) }}" class="fw-semibold text-decoration-none">{{ $property->name }}</a>
								<div class="small text-muted">{{ $property->address }}</div>
							</td>
							<td>{{ $property->city }}</td>
							<td><span class="badge {{ $workBadges[$property->work_status] ?? 'bg-secondary' }}">{{ $property->workStatusLabel() }}</span></td>
							@can ( 'manageProperties' )
								<td>
									<span class="badge {{ $property->isPublished() ? 'bg-primary' : 'bg-light text-dark border' }}">{{ $property->statusLabel() }}</span>
								</td>
								<td class="small">{{ $property->architects->pluck('name')->implode(', ') ?: 'All architects' }}</td>
							@endcan
							<td>{{ $property->plans_count }}</td>
							<td>{{ $property->licenses_count }}</td>
							<td class="text-end text-nowrap">
								<a href="{{ route('infoView', $property) }}" class="btn btn-sm btn-outline-primary" title="View"><i class="fas fa-eye"></i></a>
								@can ( 'manageProperties' )
									<a href="{{ route('infoEdit', $property) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-pen"></i></a>
									<x-form.ajax :action="route('infoDelete', $property)" method="DELETE" class="d-inline"
										data-confirm="Delete {{ $property->name }} with all its plans and licenses? This can't be undone.">
										<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
									</x-form.ajax>
								@endcan
							</td>
						</tr>
					@empty
						<tr>
							<td colspan="8" class="text-center text-muted py-4">No properties found.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if ( $properties->hasPages() )
			<div class="card-footer">
				{{ $properties->links() }}
			</div>
		@endif
	</div>

@endsection
