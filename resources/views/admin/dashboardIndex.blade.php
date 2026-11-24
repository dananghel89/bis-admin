@extends('layouts.admin')

@section('title', 'Dashboard')
@section('page', 'dashboard')

@section('content')

	<div class="alert alert-success" role="alert">
		Welcome {{ Auth::user()->name }} to our Building Information System. You are logged in!
	</div>

@endsection
