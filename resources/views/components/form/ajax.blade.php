{{--
	A form sent by public/js/ajax-form.js, with a normal post as the fallback.
	<x-form.ajax :action="route('...')" method="PATCH"> fields </x-form.ajax>
--}}
@props(['action', 'method' => 'POST', 'files' => false])

@php
	$id = $attributes->get('id', 'form-'.substr(md5($action.$method), 0, 8));
	$verb = strtoupper($method);
@endphp

<div id="{{ $id }}-success" class="alert alert-success d-none" role="status"></div>
<div id="{{ $id }}-error" class="alert alert-danger {{ $errors->has('form') ? '' : 'd-none' }}" role="alert">{{ $errors->first('form') }}</div>

<form id="{{ $id }}" method="POST" action="{{ $action }}" novalidate
	@if ( $files ) enctype="multipart/form-data" @endif
	data-ajax-form data-success="#{{ $id }}-success" data-error="#{{ $id }}-error" data-error-class="d-block" data-hidden-class="d-none"
	{{ $attributes->except('id') }}>
	@csrf
	@if ( $verb !== 'POST' )
		@method($verb)
	@endif

	{{ $slot }}
</form>
