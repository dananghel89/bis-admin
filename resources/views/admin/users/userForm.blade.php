@extends('layouts.admin')

@section('title', $user->exists ? 'Edit User' : 'Add User')
@section('page', 'users')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('users') }}">Users</a></li>
	@if ( $user->exists )
		<li class="breadcrumb-item"><a href="{{ route('userView', $user) }}">{{ $user->name }}</a></li>
	@endif
	<li class="breadcrumb-item active" aria-current="page">{{ $user->exists ? 'Edit' : 'Add' }}</li>
@endsection

@section('content')

	<div class="row">
		<div class="col-xl-8">
			<div class="card">
				<div class="card-header">
					<strong class="card-title">Account</strong>
				</div>

				<div class="card-body">
					<x-form.ajax :action="$user->exists ? route('userEditSave', $user) : route('userAddSave')" :method="$user->exists ? 'PATCH' : 'POST'">
						<x-form.input name="name" label="Name" :value="$user->name" required autocomplete="off" />
						<x-form.input name="email" label="Email Address" type="email" :value="$user->email" required autocomplete="off" />
						<x-form.select name="role" label="Role" :options="App\Models\User::ROLE_LABELS" :value="$user->role"
							help="Staff manage everything; architects see the properties made visible to them." />
						<x-form.input name="password" label="{{ $user->exists ? 'New Password' : 'Password' }}" type="password" autocomplete="new-password" :required="! $user->exists"
							:help="$user->exists ? 'Leave empty to keep the current password.' : 'At least 8 characters. Give it to the user yourself.'" />
						<x-form.input name="password_confirmation" label="Confirm Password" type="password" autocomplete="new-password" :required="! $user->exists" />

						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary">
								<i class="fas fa-save me-1"></i> {{ $user->exists ? 'Save Changes' : 'Create Account' }}
							</button>
							<a href="{{ $user->exists ? route('userView', $user) : route('users') }}" class="btn btn-outline-secondary">Cancel</a>
						</div>
					</x-form.ajax>
				</div>
			</div>

			@if ( $user->exists )
				@include('includes.admin.mail-settings', [
					'account' => $user->mailAccount,
					'saveUrl' => route('userMail', $user),
					'testUrl' => route('userMailTest', $user),
					'deleteUrl' => route('userMailDelete', $user),
				])
			@endif
		</div>
	</div>

@endsection
