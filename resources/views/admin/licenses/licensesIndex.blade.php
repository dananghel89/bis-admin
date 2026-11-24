@extends('layouts.admin')

@section('title', 'Licenses')
@section('page', 'licenses')

@section('content')

	<div class="card">
		<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
			<form method="GET" action="{{ route('licenses') }}" class="d-flex flex-wrap gap-2">
				<select name="property" class="form-select form-select-sm" aria-label="Property" style="max-width: 280px">
					<option value="">All properties</option>
					@foreach ( $properties as $id => $name )
						<option value="{{ $id }}" @selected($propertyId === $id)>{{ $name }}</option>
					@endforeach
				</select>

				<select name="state" class="form-select form-select-sm" aria-label="Expiry" style="max-width: 220px">
					<option value="">Any expiry</option>
					@foreach ( App\Http\Controllers\Admin\LicenseController::STATES as $value => $label )
						<option value="{{ $value }}" @selected($state === $value)>{{ $label }}</option>
					@endforeach
				</select>

				<button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-filter me-1"></i> Filter</button>
			</form>

			@can ( 'manageProperties' )
				<a href="{{ route('licenseAdd', ['property' => $propertyId]) }}" class="btn btn-sm btn-success"><i class="fas fa-upload me-1"></i> Upload License</a>
			@endcan
		</div>

		@include('admin.licenses.licensesTable', ['licenses' => $licenses, 'showProperty' => true])

		@if ( $licenses->hasPages() )
			<div class="card-footer">
				{{ $licenses->links() }}
			</div>
		@endif
	</div>

@endsection
