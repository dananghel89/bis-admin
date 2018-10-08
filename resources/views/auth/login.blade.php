@extends('layouts.auth')

{{-- page title --}}

@section('title', 'Access Dashboard')

{{-- content section --}}

@section('content')

	<div class="login-form">
		<form method="POST" action="{{ route('login') }}">
			{{ csrf_field() }}

			<div class="form-group{{ $errors->has('email') ? ' has-error' : '' }}">
				<label for="email">Email Address</label>
				<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email Address" required autofocus>

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
			
			<div class="checkbox">
				<label>
					<input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> Remember Me
				</label>
				
				<label class="pull-right">
					<a href="{{ route('password.request') }}">Lost your password?</a>
				</label>
			</div>

			<button type="submit" class="btn btn-success btn-flat mb-3 mt-3">Sign In</button>

			@if ( get_option('membership') )
				<div class="register-link mt-1 text-center">
					<p>Don't have an account? <a href="{{ route('register') }}">Sign Up</a></p>
				</div>
			@endif
		</form>
	</div>

@endsection
