@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Añadir Usuario')

{{-- content section --}}

@section('content')

	@cannot ( 'addUsers' )
		<div class="alert alert-danger" role="alert">
			You are not allowed to access users.
		</div>
	@else
		<div class="alert alert-primary" role="alert">
			TODO: coming soon
		</div>
	@endcan

@endsection
