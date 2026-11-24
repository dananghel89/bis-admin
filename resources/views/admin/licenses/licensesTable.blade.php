{{-- A list of licenses with expiry badges and download (and, for staff, edit/delete) buttons. Used on Licenses and on a property page. --}}
<div class="table-responsive">
	<table class="table table-hover align-middle mb-0">
		<thead>
			<tr>
				<th scope="col">License</th>
				@if ( $showProperty )
					<th scope="col">Property</th>
				@endif
				<th scope="col">Number</th>
				<th scope="col">Issued</th>
				<th scope="col">Expires</th>
				<th scope="col" class="text-end">Actions</th>
			</tr>
		</thead>

		<tbody>
			@forelse ( $licenses as $license )
				<tr>
					<td>
						<i class="far fa-file-pdf text-danger me-1"></i>
						<a href="{{ route('licenseDownload', $license) }}" class="fw-semibold text-decoration-none">{{ $license->title }}</a>
					</td>
					@if ( $showProperty )
						<td><a href="{{ route('infoView', $license->property_id) }}" class="text-decoration-none">{{ $license->property->name }}</a></td>
					@endif
					<td class="small">{{ $license->number ?: '—' }}</td>
					<td class="small text-nowrap">{{ $license->issued_at?->format('Y-m-d') ?? '—' }}</td>
					<td class="small text-nowrap">
						{{ $license->expires_at?->format('Y-m-d') ?? '—' }}
						@if ( $license->isExpired() )
							<span class="badge bg-danger ms-1">Expired</span>
						@elseif ( $license->isExpiringSoon() )
							<span class="badge bg-warning text-dark ms-1">Expires in {{ trans_choice(':count day|:count days', (int) today()->diffInDays($license->expires_at)) }}</span>
						@endif
					</td>
					<td class="text-end text-nowrap">
						<a href="{{ route('licenseDownload', $license) }}" class="btn btn-sm btn-outline-primary" title="Download {{ $license->file_name }}"><i class="fas fa-download"></i></a>
						@can ( 'manageProperties' )
							<a href="{{ route('licenseEdit', $license) }}" class="btn btn-sm btn-outline-secondary" title="Edit"><i class="fas fa-pen"></i></a>
							<x-form.ajax :action="route('licenseDelete', $license)" method="DELETE" class="d-inline" data-confirm="Delete the license {{ $license->title }}?">
								<button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
							</x-form.ajax>
						@endcan
					</td>
				</tr>
			@empty
				<tr>
					<td colspan="6" class="text-center text-muted py-4">No licenses yet.</td>
				</tr>
			@endforelse
		</tbody>
	</table>
</div>
