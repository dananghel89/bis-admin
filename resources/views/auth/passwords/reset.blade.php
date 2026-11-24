@extends('layouts.auth')

@section('title', 'Reset Password')
@section('subtitle', 'Choose a new password')

@section('content')

	<x-form.ajax :action="url('/password/reset')">
		<input type="hidden" name="token" value="{{ $token }}">

		<x-form.input name="email" label="Email address" type="email" :value="$email" floating required autocomplete="username" />
		<x-form.input name="password" label="New password" type="password" floating required autofocus autocomplete="new-password" />
		<x-form.input name="password_confirmation" label="Confirm new password" type="password" floating required autocomplete="new-password" />

		<button type="submit" class="auth-btn">Reset Password</button>
	</x-form.ajax>

@endsection

@section('footer')
	<a href="{{ route('login') }}">Back to Log In</a>
@endsection
