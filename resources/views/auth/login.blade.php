@extends('layouts.auth')

@section('title', 'Welcome Back')
@section('subtitle', 'Sign in to your account')

@section('content')

	<x-form.ajax :action="route('login')" id="loginForm">
		<x-form.input name="email" label="Email address" type="email" floating required autofocus autocomplete="username" />

		<div class="position-relative">
			<x-form.input name="password" label="Password" type="password" floating required autocomplete="current-password" />
			<button type="button" class="btn btn-link position-absolute end-0 top-0 mt-2 me-2 p-1 text-secondary" data-toggle-password="password" aria-label="Show or hide the password" style="z-index: 5">
				<i class="fas fa-eye"></i>
			</button>
		</div>

		<div class="d-flex justify-content-between align-items-center mb-3">
			<div class="form-check mb-0">
				<input class="form-check-input" type="checkbox" id="remember" name="remember" @checked(old('remember'))>
				<label class="form-check-label" for="remember">Remember me</label>
			</div>

			<a href="{{ route('password.request') }}" class="small fw-semibold text-decoration-none">Forgot password?</a>
		</div>

		<button type="submit" class="auth-btn">Sign In</button>
	</x-form.ajax>

@endsection

@section('footer')
	<a href="{{ route('front') }}">Back to the website</a>
@endsection
