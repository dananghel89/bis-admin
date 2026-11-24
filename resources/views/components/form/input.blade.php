{{--
	<x-form.input name="email" label="Email Address" type="email" :value="$user->email" required />
	`floating` uses Bootstrap's floating label (the auth pages); `help` adds a hint under the field.
--}}
@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'floating' => false])

@php
	$id = $attributes->get('id', $name);
	$key = str_replace(['[]', '[', ']'], ['', '.', ''], $name);
	$current = $type === 'password' ? null : old($key, $value);
@endphp

<div class="{{ $floating ? 'form-floating' : '' }} mb-3">
	@unless ( $floating )
		<label for="{{ $id }}" class="form-label">{{ $label }}</label>
	@endunless

	<input type="{{ $type }}" name="{{ $name }}" id="{{ $id }}"
		@if ( ! is_null($current) ) value="{{ $current }}" @endif
		@if ( $floating ) placeholder="{{ $label }}" @endif
		{{ $attributes->except('id')->class(['form-control', 'is-invalid' => $errors->has($key)]) }}>

	@if ( $floating )
		<label for="{{ $id }}">{{ $label }}</label>
	@endif

	<x-form.error :name="$name" />

	@if ( $help )
		<div class="form-text">{{ $help }}</div>
	@endif
</div>
