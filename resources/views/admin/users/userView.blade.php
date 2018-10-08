@extends('layouts.admin')

{{-- page title --}}

@section('title', 'View User')

{{-- content section --}}

@section('content')

	@cannot ( 'viewUsers' )
		<div class="alert alert-danger" role="alert">
			You are not allowed to access users.
		</div>
	@else
		<div class="alert alert-primary" role="alert">
			TODO: coming soon
		</div>
	@endcan

@endsection
