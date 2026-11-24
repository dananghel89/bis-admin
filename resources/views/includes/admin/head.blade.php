<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ config('project.site_title') }} - @yield('title') | {{ config('project.tagline') }}</title>

<link rel="icon" href="{{ asset('favicon.ico') }}">

{{-- Paint the saved light/dark theme before the theme script (a deferred module) runs. --}}
<script>
	try {
		var theme = localStorage.getItem('sufee-theme');
		if (theme !== 'light' && theme !== 'dark') {
			theme = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
		}
		document.documentElement.setAttribute('data-bs-theme', theme);
	} catch (e) {}
</script>

{{-- Sufee 3.2.1, built once from its source (see README); the file names change with each build. --}}
<link rel="stylesheet" href="{{ asset('admin/themify-icons/themify-icons.css') }}">
<link rel="stylesheet" href="{{ asset('admin/assets/main-BW119va0.css') }}">
<link rel="stylesheet" href="{{ asset('css/bis-admin.css') }}">
<script type="module" src="{{ asset('admin/assets/main-C9NkpTPF.js') }}"></script>
<script src="{{ asset('js/ajax-form.js') }}" defer></script>

@stack('head')
