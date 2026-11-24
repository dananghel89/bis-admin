<!DOCTYPE html>

{{-- The theme's error card (error-404.html). Kept light: a 500 page may render while the database is down. --}}
<html lang="{{ app()->getLocale() }}" data-bs-theme="light">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>{{ config('project.site_title') }} - @yield('title')</title>
	<link rel="icon" href="{{ asset('favicon.ico') }}">
	<script>
		try {
			var theme = localStorage.getItem('sufee-theme');
			if (theme === 'light' || theme === 'dark') { document.documentElement.setAttribute('data-bs-theme', theme); }
		} catch (e) {}
	</script>
	<link rel="stylesheet" href="{{ asset('admin/assets/main-BW119va0.css') }}">
	<link rel="stylesheet" href="{{ asset('css/bis-admin.css') }}">
</head>

<body class="error-page">
	<main class="error-card">
		<div class="error-illustration">
			<i class="fas @yield('icon', 'fa-exclamation-triangle')"></i>
		</div>
		<div class="error-number">@yield('code')</div>
		<h1 class="error-title">@yield('title')</h1>
		<p class="error-message">@yield('message')</p>

		<div class="error-actions">
			<a href="{{ url('/dashboard') }}" class="btn-error btn-error-primary">
				<i class="fas fa-home"></i> Back to Dashboard
			</a>
			<button type="button" class="btn-error btn-error-secondary" onclick="history.back()">
				<i class="fas fa-arrow-left"></i> Go Back
			</button>
		</div>
	</main>
</body>

</html>
