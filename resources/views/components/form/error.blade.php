{{-- The error slot of one field; js/ajax-form.js fills it after an ajax post. --}}
@props(['name'])

@php($key = str_replace(['[]', '[', ']'], ['', '.', ''], $name))

<div class="invalid-feedback {{ $errors->has($key) ? 'd-block' : '' }}" data-error-for="{{ $key }}">{{ $errors->first($key) }}</div>
