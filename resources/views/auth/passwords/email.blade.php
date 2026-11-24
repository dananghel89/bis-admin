@extends('layouts.auth')

@section('title', 'Forgot Password?')
@section('subtitle', 'We will email you a link to choose a new one')

@section('content')

	<x-form.ajax :action="route('password.email')" data-reset>
		<x-form.input name="email" label="Email address" type="email" floating required autofocus autocomplete="username" />

		<button type="submit" class="auth-btn">Send Password Reset Link</button>
	</x-form.ajax>

@endsection

@section('footer')
	<a href="{{ route('login') }}">Back to Log In</a>
@endsection
