{{-- <x-form.textarea name="description" label="Description" :value="$property->description" rows="5" /> --}}
@props(['name', 'label', 'value' => null, 'help' => null])

@php($id = $attributes->get('id', $name))

<div class="mb-3">
	<label for="{{ $id }}" class="form-label">{{ $label }}</label>

	<textarea name="{{ $name }}" id="{{ $id }}" {{ $attributes->except('id')->merge(['rows' => 4])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>{{ old($name, $value) }}</textarea>

	<x-form.error :name="$name" />

	@if ( $help )
		<div class="form-text">{{ $help }}</div>
	@endif
</div>
