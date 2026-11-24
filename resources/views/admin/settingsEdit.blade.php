@extends('layouts.admin')

@section('title', 'Settings')
@section('page', 'settings')

@section('content')

	<div class="row">
		<div class="col-xl-8">
			<div class="card">
				<div class="card-header">
					<strong class="card-title">Account</strong>
				</div>

				<div class="card-body">
					<x-form.ajax :action="route('settingsEdit')" method="PATCH">
						<div class="mb-3">
							<label class="form-label">Role</label>
							<input type="text" class="form-control" value="{{ $role }}" disabled>
						</div>

						<x-form.input name="name" label="Name" :value="$name" required autocomplete="name" />
						<x-form.input name="email" label="Email Address" type="email" :value="$email" required autocomplete="email" />
						<x-form.input name="password" label="New Password" type="password" autocomplete="new-password" help="Leave empty to keep your current password." />
						<x-form.input name="password_confirmation" label="Confirm New Password" type="password" autocomplete="new-password" />

						<button type="submit" class="btn btn-primary">
							<i class="fas fa-save me-1"></i> Update Profile
						</button>
					</x-form.ajax>
				</div>
			</div>
		</div>
	</div>

@endsection
