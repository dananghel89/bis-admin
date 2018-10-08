@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Ajustes')

{{-- content section --}}

@section('content')

	@if ( $message = Session::get('success') )
		<div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
			<span>{{ $message }}</span>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif

	<div class="card_medium_form">
		<div class="card">
			<form method="post" action="{{ route('settingsEdit') }}" class="form-horizontal">
				{{ method_field('PATCH') }}
				{{ csrf_field() }}

				<div class="card-body card-block">
					<div class="row form-group">
						<div class="col-3">
							<label class="form-control-label">Role</label>
						</div>
						
						<div class="col-9">
							<input type="text" name="disablad_role" disabled class="form-control" value="{{ $role }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="name" class="form-control-label">Name</label>
						</div>
						
						<div class="col-9">
							<input type="text" id="name" name="name" placeholder="Name" class="form-control" value="{{ !empty(old('name')) ? old('name') : $name }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="email" class="form-control-label">Email Address</label>
						</div>
						
						<div class="col-9">
							<input type="email" id="email" name="email" placeholder="Email Address" class="form-control" value="{{ !empty(old('email')) ? old('email') : $email }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="password" class="form-control-label">Password</label>
						</div>
						
						<div class="col-9">
							<input type="password" id="password" name="password" placeholder="Password" class="form-control">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="password_confirmation">Confirm Password</label>
						</div>
						
						<div class="col-9">
							<input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password" class="form-control">
						</div>
					</div>

					@if ( count($errors) > 0 )
						<div class="row form-group mb-0">
							<div class="col-3">
								<label class="form-control-label">Validation</label>
							</div>

							<div class="col-9">
								<div class="alert alert-danger mb-0" role="alert">
									@foreach ( $errors->all() as $error )
										<p class="mb-0">{{ $error }}</p>
									@endforeach
								</div>
							</div>
						</div>
					@endif
				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary btn-sm">
						<i class="fa fa-dot-circle-o"></i> Update Profile
					</button>
				</div>
			</form>
		</div>
	</div>

@endsection
