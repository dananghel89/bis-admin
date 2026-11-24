{{-- Licenses expired or expiring within License::EXPIRING_DAYS days, on the dashboard. --}}
<div class="card h-100">
	<div class="card-header d-flex justify-content-between align-items-center">
		<strong class="card-title">Licenses to Check</strong>
		<a href="{{ route('licenses', ['state' => 'attention']) }}" class="small">See all</a>
	</div>
	<ul class="list-group list-group-flush">
		@forelse ( $licenses as $license )
			<li class="list-group-item d-flex justify-content-between align-items-start">
				<div>
					<a href="{{ route('licenseDownload', $license) }}" class="fw-semibold text-decoration-none">{{ $license->title }}</a>
					<div class="small"><a href="{{ route('infoView', $license->property_id) }}" class="text-muted text-decoration-none">{{ $license->property?->name }}</a></div>
				</div>
				<span class="badge {{ $license->isExpired() ? 'bg-danger' : 'bg-warning text-dark' }}">
					{{ $license->isExpired() ? 'Expired' : 'Expires' }} {{ $license->expires_at->format('Y-m-d') }}
				</span>
			</li>
		@empty
			<li class="list-group-item text-muted">Nothing to check: no license is expired or expiring within {{ App\Models\License::EXPIRING_DAYS }} days.</li>
		@endforelse
	</ul>
</div>
