@extends('layouts.auth')

{{-- page title --}}

@section('title', 'Lost Password')

{{-- content section --}}

@section('content')

	<div class="login-form">
		@if (session('status'))
			<div class="alert alert-success">
				{{ session('status') }}
			</div>
		@endif

		<form method="POST" action="{{ route('password.email') }}">
			{{ csrf_field() }}

			<div class="form-group">
				<label for="email">Email Address</label>
				<input id="email" type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email Address" required autofocus>
			
				@if ($errors->has('email'))
					<small class="help-block form-text text-danger">{{ $errors->first('email') }}</small>
				@endif
			</div>
			
			<button type="submit" class="btn btn-primary btn-flat mb-3 mt-3">Send Password Reset Link</button>
			
			<div class="register-link mt-1 text-center">
				<p><a href="{{ route('login') }}">Back to Log In</a></p>
			</div>
		</form>
	</div>

@endsection
