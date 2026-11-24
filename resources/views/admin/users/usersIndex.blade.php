@extends('layouts.admin')

@section('title', 'Users')
@section('page', 'users')

@section('content')

	<div class="card">
		<div class="card-header d-flex flex-wrap gap-2 justify-content-between align-items-center">
			<form method="GET" action="{{ route('users') }}" class="d-flex flex-wrap gap-2" role="search">
				<input type="search" name="search" value="{{ $search }}" class="form-control form-control-sm" placeholder="Name or email" aria-label="Search users" style="max-width: 220px">
				<select name="role" class="form-select form-select-sm" aria-label="Role" style="max-width: 160px">
					<option value="">All roles</option>
					@foreach ( App\Models\User::ROLE_LABELS as $value => $label )
						<option value="{{ $value }}" @selected((string) $role === (string) $value)>{{ $label }}</option>
					@endforeach
				</select>
				<button type="submit" class="btn btn-sm btn-outline-secondary"><i class="fas fa-search me-1"></i> Filter</button>
			</form>

			<a href="{{ route('userAdd') }}" class="btn btn-sm btn-success">
				<i class="fas fa-plus me-1"></i> Add User
			</a>
		</div>

		<div class="table-responsive">
			<table class="table table-hover align-middle mb-0">
				<thead>
					<tr>
						<th scope="col">Name</th>
						<th scope="col">Email</th>
						<th scope="col">Role</th>
						<th scope="col" class="text-end">Properties</th>
						<th scope="col">Created</th>
					</tr>
				</thead>

				<tbody>
					@forelse ( $users as $user )
						<tr>
							<td>
								<a href="{{ route('userView', $user) }}" class="fw-semibold text-decoration-none">{{ $user->name }}</a>
								@if ( $user->is(Auth::user()) )
									<span class="badge bg-secondary ms-1">You</span>
								@endif
							</td>
							<td>{{ $user->email }}</td>
							<td>
								<span class="badge {{ $user->isStaff() ? 'bg-primary' : 'bg-info text-dark' }}">{{ $user->roleLabel() }}</span>
							</td>
							<td class="text-end">{{ $user->isArchitect() ? $user->properties_count : '—' }}</td>
							<td>{{ $user->created_at?->format('Y-m-d') }}</td>
						</tr>
					@empty
						<tr>
							<td colspan="5" class="text-center text-muted py-4">No users found.</td>
						</tr>
					@endforelse
				</tbody>
			</table>
		</div>

		@if ( $users->hasPages() )
			<div class="card-footer">
				{{ $users->links() }}
			</div>
		@endif
	</div>

@endsection
