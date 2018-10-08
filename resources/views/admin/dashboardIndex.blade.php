@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Dashboard')

{{-- content section --}}

@section('content')

	<div class="alert alert-success" role="alert">
		Welcome {{ Auth::user()->name }} to our Building Information System. You are logged in!
	</div>

@endsection
