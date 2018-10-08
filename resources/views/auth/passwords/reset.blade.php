@extends('layouts.auth')

{{-- page title --}}

@section('title', 'Reset Password')

{{-- content section --}}

@section('content')

	<div class="login-form">
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
		@endif

		<form method="POST" action="{{ route('password.request') }}">
			{{ csrf_field() }}

			<input type="hidden" name="token" value="{{ $token }}">

			<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
				<label for="email">Email Address</label>
				<input id="email" type="email" class="form-control" name="email" value="{{ $email or old('email') }}" placeholder="Email Address" required autofocus>

				@if ($errors->has('email'))
					<small class="help-block form-text text-danger">{{ $errors->first('email') }}</small>
				@endif
			</div>

			<div class="form-group{{ $errors->has('password') ? ' has-error' : '' }}">
				<label for="password">Password</label>
				<input id="password" type="password" class="form-control" name="password" placeholder="Password" required>

				@if ($errors->has('password'))
					<small class="help-block form-text text-danger">{{ $errors->first('password') }}</small>
				@endif
			</div>

			<div class="form-group{{ $errors->has('password_confirmation') ? ' has-error' : '' }}">
				<label for="password-confirm">Confirm Password</label>
				<input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password" required>

				@if ($errors->has('password_confirmation'))
					<small class="help-block form-text text-danger">{{ $errors->first('password_confirmation') }}</small>
				@endif
			</div>
			
			<button type="submit" class="btn btn-primary btn-flat mb-3 mt-3">Reset Password</button>
		</form>
	</div>

@endsection
