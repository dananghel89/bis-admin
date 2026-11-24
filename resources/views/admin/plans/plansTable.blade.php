{{-- A list of plans with download (and, for staff, edit/delete) buttons. Used on Plans and on a property page. --}}
<div class="table-responsive">
	<table class="table table-hover align-middle mb-0">
		<thead>
			<tr>
				<th scope="col">Plan</th>
				@if ( $showProperty )
					<th scope="col">Property</th>
				@endif
				<th scope="col">Size</th>
				<th scope="col">Uploaded</th>
				<th scope="col" class="text-end">Actions</th>
			</tr>
		</thead>

		<tbody>
			@forelse ( $plans as $plan )
				<tr>
					<td>
						<i class="far fa-file-pdf text-danger me-1"></i>
						<a href="{{ route('planDownload', $plan) }}" class="fw-semibold text-decoration-none">{{ $plan->title }}</a>
						@if ( $plan->description )
							<div class="small text-muted">{{ $plan->description }}</div>
						@endif
					</td>
					@if ( $showProperty )
						<td><a href="{{ route('infoView', $plan->property_id) }}" class="text-decoration-none">{{ $plan->property->name }}</a></td>
					@endif
					<td class="small text-nowrap">{{ $plan->fileSizeForHumans() }}</td>
					<td class="small text-nowrap">{{ $plan->created_at?->format('Y-m-d') }}</td>
					<td class="text-end text-nowrap">
						<a href="{{ route('planDownload', $plan) }}" class="btn btn-sm btn-outline-primary" title="Download {{ $plan->file_name }}"><i class="fas fa-download"></i></a>
						@can ( 'manageProperties' )
							<a href="{{ route('planEdit', $plan) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-pen"></i></a>
							<x-form.ajax :action="route('planDelete', $plan)" method="DELETE" class="d-inline" data-confirm="Delete the plan {{ $plan->title }}?">
								<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
							</x-form.ajax>
						@endcan
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="5" class="text-center text-muted py-4">No plans yet.</td>
				</tr>
			@endforelse
		</tbody>
	</table>
</div>
