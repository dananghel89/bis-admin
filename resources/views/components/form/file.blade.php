{{-- <x-form.file name="file" label="PDF" accept="application/pdf" help="..." /> --}}
@props(['name', 'label', 'help' => null])

@php($id = $attributes->get('id', $name))

<div class="mb-3">
	<label for="{{ $id }}" class="form-label">{{ $label }}</label>

	<input type="file" name="{{ $name }}" id="{{ $id }}" {{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>

	<x-form.error :name="$name" />

	@if ( $help )
		<div class="form-text">{{ $help }}</div>
	@endif
</div>
