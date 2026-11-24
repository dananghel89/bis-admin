@extends('layouts.admin')

@section('title', $property->exists ? 'Edit Property' : 'Add Property')
@section('page', 'infos')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('infos') }}">Infos</a></li>
	@if ( $property->exists )
		<li class="breadcrumb-item"><a href="{{ route('infoView', $property) }}">{{ $property->name }}</a></li>
	@endif
	<li class="breadcrumb-item active" aria-current="page">{{ $property->exists ? 'Edit' : 'Add' }}</li>
@endsection

@include('includes.admin.map-assets')

@section('content')

	<x-form.ajax :action="$property->exists ? route('infoEditSave', $property) : route('infoAddSave')" :method="$property->exists ? 'PATCH' : 'POST'">
		<div class="row g-4">
			<div class="col-xl-7">
				<div class="card">
					<div class="card-header"><strong class="card-title">Property</strong></div>

					<div class="card-body">
						<x-form.input name="name" label="Name" :value="$property->name" required maxlength="255" />
						<x-form.input name="address" label="Address" :value="$property->address" required maxlength="255" />

						<div class="row">
							<div class="col-sm-4">
								<x-form.input name="postal_code" label="Postal Code" :value="$property->postal_code" maxlength="20" />
							</div>
							<div class="col-sm-8">
								<x-form.input name="city" label="City" :value="$property->city" required maxlength="100" />
							</div>
						</div>

						<x-form.textarea name="description" label="Description" :value="$property->description" rows="6" />
					</div>
				</div>

				<div class="card mt-4">
					<div class="card-header"><strong class="card-title">Access</strong></div>

					<div class="card-body">
						<x-form.select name="status" label="Status" :options="App\Models\Property::STATUSES" :value="$property->status"
							help="Drafts are only visible to staff." />

						<x-form.select name="architects[]" label="Architects" :options="$architects" :value="$property->architects->pluck('id')->all()"
							multiple size="6" id="architects"
							help="Hold Ctrl (Cmd on a Mac) to choose several. With none chosen, every architect sees the published property." />
					</div>
				</div>
			</div>

			<div class="col-xl-5">
				<div class="card">
					<div class="card-header"><strong class="card-title">Location</strong></div>

					<div class="card-body">
						<p class="small text-muted">Click the map to set the location, or drag the pin.</p>

						<div class="rounded border mb-3" style="height: 340px"
							data-map
							data-center="{{ json_encode(config('project.map_center')) }}"
							data-picker='{"lat":"#latitude","lng":"#longitude"}'></div>

						<div class="row">
							<div class="col-6">
								<x-form.input name="latitude" label="Latitude" :value="$property->latitude" inputmode="decimal" />
							</div>
							<div class="col-6">
								<x-form.input name="longitude" label="Longitude" :value="$property->longitude" inputmode="decimal" />
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<div class="d-flex gap-2 mt-4">
			<button type="submit" class="btn btn-primary">
				<i class="fas fa-save me-1"></i> {{ $property->exists ? 'Save Changes' : 'Add Property' }}
			</button>
			<a href="{{ $property->exists ? route('infoView', $property) : route('infos') }}" class="btn btn-outline-secondary">Cancel</a>
		</div>
	</x-form.ajax>

@endsection
