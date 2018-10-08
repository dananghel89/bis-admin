@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Añadir Plan')

{{-- content section --}}

@section('content')

	@cannot ( 'addPosts' )
		<div class="alert alert-danger" role="alert">
			You are not allowed to access info.
		</div>
	@else
		<div class="alert alert-primary" role="alert">
			TODO: coming soon
		</div>
	@endcan

@endsection
