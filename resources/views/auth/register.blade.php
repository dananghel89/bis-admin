@extends('layouts.auth')

{{-- page title --}}

@section('title', 'Dashboard Sign Up')

{{-- content section --}}

@section('content')

	<div class="login-form">
		<form class="form-horizontal" method="POST" action="{{ route('register') }}">
			{{ csrf_field() }}

			<div class="form-group{{ $errors->has('name') ? ' has-error' : '' }}">
				<label for="name">Name</label>
				<input id="name" type="text" class="form-control" name="name" value="{{ old('name') }}" placeholder="Name" required autofocus>

				@if ($errors->has('name'))
					<small class="help-block form-text text-danger">{{ $errors->first('name') }}</small>
				@endif
			</div>

			<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
				<label for="email">Email Address</label>
				<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email Address" required>

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

			<div class="form-group">
				<label for="password-confirm">Confirm Password</label>
				<input id="password-confirm" type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password" required>
			</div>

			<div class="checkbox">
				<label>
					<input type="checkbox" name="terms" value="1">
					Agree to our <a href="{{ route('tos') }}">Terms of Service</a>

					@if ($errors->has('terms'))
						<small class="help-block form-text text-danger">{{ $errors->first('terms') }}</small>
					@endif
				</label>
			</div>

			<button type="submit" class="btn btn-primary btn-flat mb-3 mt-3">Register</button>

			<div class="register-link mt-1 text-center">
				<p>Already have an account? <a href="{{ route('login') }}">Log In</a></p>
			</div>
		</form>
	</div>

@endsection
