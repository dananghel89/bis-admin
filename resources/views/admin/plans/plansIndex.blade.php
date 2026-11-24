@extends('layouts.admin')

@section('title', 'Plans')
@section('page', 'plans')

@include('includes.admin.map-assets')

@section('content')

	<div class="card mb-4">
		<div class="card-header d-flex justify-content-between align-items-center">
			<strong class="card-title">Map</strong>
			<span class="small text-muted">{{ trans_choice(':count property|:count properties', count($markers)) }} on the map</span>
		</div>

		<div style="height: 420px" data-map
			data-center="{{ json_encode(config('project.map_center')) }}"
			data-markers="{{ json_encode($markers) }}"></div>
	</div>

	<div class="card">
		<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
			<form method="GET" action="{{ route('plans') }}" class="d-flex flex-wrap gap-2">
				<select name="property" class="form-select form-select-sm" aria-label="Property" style="max-width: 280px">
					<option value="">All properties</option>
					@foreach ( $properties as $id => $name )
						<option value="{{ $id }}" @selected($propertyId === $id)>{{ $name }}</option>
					@endforeach
				</select>
				<button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter me-1"></i> Filter</button>
			</form>

			@can ( 'manageProperties' )
				<a href="{{ route('planAdd', ['property' => $propertyId]) }}" class="btn btn-sm btn-success"><i class="fas fa-upload me-1"></i> Upload Plan</a>
			@endcan
		</div>

		@include('admin.plans.plansTable', ['plans' => $plans, 'showProperty' => true])

		@if ( $plans->hasPages() )
			<div class="card-footer">
				{{ $plans->links() }}
			</div>
		@endif
	</div>

@endsection
