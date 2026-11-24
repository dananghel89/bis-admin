{{--
	<x-form.select name="role" label="Role" :options="[0 => 'Staff', 1 => 'Architect']" :value="$user->role" />
	With `multiple`, name it "items[]" and pass an array as the value.
--}}
@props(['name', 'label', 'options' => [], 'value' => null, 'placeholder' => null, 'help' => null])

@php
	$id = $attributes->get('id', str_replace(['[', ']'], ['_', ''], $name));
	$key = str_replace(['[]', '[', ']'], ['', '.', ''], $name);
	$selected = array_map('strval', (array) old($key, $value));
	$hasError = $errors->has($key) || $errors->has($key.'.*');
@endphp

<div class="mb-3">
	<label for="{{ $id }}" class="form-label">{{ $label }}</label>

	<select name="{{ $name }}" id="{{ $id }}" {{ $attributes->except('id')->class(['form-select', 'is-invalid' => $hasError]) }}>
		@if ( ! is_null($placeholder) )
			<option value="">{{ $placeholder }}</option>
		@endif

		@foreach ( $options as $optionValue => $optionLabel )
			<option value="{{ $optionValue }}" @selected(in_array((string) $optionValue, $selected, true))>{{ $optionLabel }}</option>
		@endforeach
	</select>

	<x-form.error :name="$name" />

	@if ( $help )
		<div class="form-text">{{ $help }}</div>
	@endif
</div>
