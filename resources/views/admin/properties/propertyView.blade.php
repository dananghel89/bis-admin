@extends('layouts.admin')

@section('title', $property->name)
@section('page', 'infos')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('infos') }}">Infos</a></li>
	<li class="breadcrumb-item active" aria-current="page">{{ $property->name }}</li>
@endsection

@php
	$workBadges = ['pending' => 'bg-secondary', 'in_progress' => 'bg-warning text-dark', 'done' => 'bg-success'];
@endphp

@include('includes.admin.map-assets')

@section('content')

	<div class="row g-4">
		<div class="col-xl-7">
			<div class="card">
				<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
					<strong class="card-title">Property</strong>
					<div class="d-flex gap-2">
						@can ( 'manageProperties' )
							<span class="badge {{ $property->isPublished() ? 'bg-primary' : 'bg-light text-dark border' }}">{{ $property->statusLabel() }}</span>
						@endcan
						<span class="badge {{ $workBadges[$property->work_status] ?? 'bg-secondary' }}">{{ $property->workStatusLabel() }}</span>
					</div>
				</div>

				<div class="card-body">
					<dl class="row mb-0">
						<dt class="col-sm-3">Address</dt>
						<dd class="col-sm-9">{{ $property->fullAddress() }}</dd>

						@if ( $property->description )
							<dt class="col-sm-3">Description</dt>
							<dd class="col-sm-9">{!! nl2br(e($property->description)) !!}</dd>
						@endif

						@can ( 'manageProperties' )
							<dt class="col-sm-3">Architects</dt>
							<dd class="col-sm-9">
								@forelse ( $property->architects as $architect )
									<a href="{{ route('userView', $architect) }}" class="badge bg-info text-dark text-decoration-none">{{ $architect->name }}</a>
								@empty
									<span class="text-muted">None: every architect sees it when published.</span>
								@endforelse
							</dd>

							<dt class="col-sm-3">Added</dt>
							<dd class="col-sm-9 mb-0">{{ $property->created_at?->format('Y-m-d') }}{{ $property->creator ? ' by '.$property->creator->name : '' }}</dd>
						@endcan
					</dl>
				</div>

				<div class="card-footer d-flex flex-wrap gap-2">
					@can ( 'manageProperties' )
						<a href="{{ route('infoEdit', $property) }}" class="btn btn-sm btn-primary"><i class="fas fa-pen me-1"></i> Edit</a>

						<x-form.ajax :action="route('infoDelete', $property)" method="DELETE" class="d-inline"
							data-confirm="Delete {{ $property->name }} with all its plans and licenses? This can't be undone.">
							<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i> Delete</button>
						</x-form.ajax>
					@endcan

					<a href="{{ route('infos') }}" class="btn btn-sm btn-outline-secondary ms-auto"><i class="fas fa-angle-left me-1"></i> Back to Infos</a>
				</div>
			</div>

			<div class="card mt-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<strong class="card-title">Plans</strong>
					@can ( 'manageProperties' )
						<a href="{{ route('planAdd', ['property' => $property->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-upload me-1"></i> Upload Plan</a>
					@endcan
				</div>

				@include('admin.plans.plansTable', ['plans' => $property->plans()->latest()->get(), 'showProperty' => false])
			</div>

			<div class="card mt-4">
				<div class="card-header d-flex justify-content-between align-items-center">
					<strong class="card-title">Licenses</strong>
					@can ( 'manageProperties' )
						<a href="{{ route('licenseAdd', ['property' => $property->id]) }}" class="btn btn-sm btn-success"><i class="fas fa-upload me-1"></i> Upload License</a>
					@endcan
				</div>

				@include('admin.licenses.licensesTable', ['licenses' => $property->licenses()->orderByRaw('expires_at is null')->orderBy('expires_at')->get(), 'showProperty' => false])
			</div>
		</div>

		<div class="col-xl-5">
			<div class="card">
				<div class="card-header"><strong class="card-title">Location</strong></div>

				@if ( $property->hasLocation() )
					@php($marker = ['lat' => $property->latitude, 'lng' => $property->longitude, 'title' => $property->name, 'subtitle' => $property->fullAddress()])
					<div style="height: 280px" data-map data-center="{{ json_encode(config('project.map_center')) }}" data-markers="{{ json_encode([$marker]) }}"></div>
				@else
					<div class="card-body text-muted">No location set.</div>
				@endif
			</div>

			<div class="card mt-4">
				<div class="card-header"><strong class="card-title">Work Status</strong></div>

				<div class="card-body">
					@can ( 'updateWorkStatus' )
						<x-form.ajax :action="route('infoWorkStatus', $property)" method="PATCH">
							<x-form.select name="work_status" label="Change to" :options="array_diff_key(App\Models\Property::WORK_STATUSES, [$property->work_status => true])" />
							<x-form.textarea name="note" label="Note" rows="2" maxlength="1000" placeholder="Optional: what was done" />

							<button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-check me-1"></i> Update Status</button>
						</x-form.ajax>
					@endcan
				</div>

				<ul class="list-group list-group-flush">
					@forelse ( $property->statusChanges as $change )
						<li class="list-group-item">
							<div class="d-flex justify-content-between">
								<span>
									{{ App\Models\Property::WORK_STATUSES[$change->from_status] ?? $change->from_status }}
									<i class="fas fa-arrow-right mx-1 small text-muted"></i>
									<strong>{{ App\Models\Property::WORK_STATUSES[$change->to_status] ?? $change->to_status }}</strong>
								</span>
								<small class="text-muted">{{ $change->created_at?->format('Y-m-d H:i') }}</small>
							</div>
							<div class="small text-muted">{{ $change->user?->name ?? 'Deleted user' }}</div>
							@if ( $change->note )
								<div class="small mt-1">{!! nl2br(e($change->note)) !!}</div>
							@endif
						</li>
					@empty
						<li class="list-group-item text-muted small">No changes yet.</li>
					@endforelse
				</ul>
			</div>
		</div>
	</div>

@endsection
