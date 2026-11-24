{{-- Leaflet and the map script, for pages with a [data-map] element. --}}
@push('head')
	<link rel="stylesheet" href="{{ asset('vendor/leaflet/leaflet.css') }}">
@endpush

@push('scripts')
	<script src="{{ asset('vendor/leaflet/leaflet.js') }}"></script>
	<script src="{{ asset('js/property-map.js') }}"></script>
@endpush
