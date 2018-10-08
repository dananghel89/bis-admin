@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Enviar Mensaje')

{{-- content section --}}

@section('content')

	@cannot ( 'accessMessages' )
		<div class="alert alert-danger" role="alert">
			You are not allowed to access messages.
		</div>
	@else
		<div class="alert alert-primary" role="alert">
			TODO: coming soon
		</div>
	@endcan

@endsection
