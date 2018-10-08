@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Edit User')

{{-- content section --}}

@section('content')

	@cannot ( 'editUsers' )
		<div class="alert alert-danger" role="alert">
			You are not allowed to access users.
		</div>
	@else
		<div class="alert alert-primary" role="alert">
			TODO: coming soon
		</div>
	@endcan

@endsection
