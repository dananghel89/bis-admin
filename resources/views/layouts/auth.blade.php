<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" data-bs-theme="light">

<head>
	@include('includes.admin.head')
</head>

<body class="auth-page">
	{{-- The theme's auth card (page-login.html) --}}
	<main class="auth-card">
		<header class="auth-hero">
			<a href="{{ route('front') }}" class="text-decoration-none">
				<span class="auth-logo d-inline-block fs-2 fw-bold">{{ config('project.site_title') }}</span>
			</a>
			<h1 class="auth-title">@yield('title')</h1>
			@hasSection('subtitle')
				<p class="auth-subtitle">@yield('subtitle')</p>
			@endif
		</header>

		<div class="auth-body">
			@if ( session('status') )
				<div class="alert alert-success" role="status">{{ session('status') }}</div>
			@endif

			@yield('content')
		</div>

		@hasSection('footer')
			<footer class="auth-footer">
				@yield('footer')
			</footer>
		@endif
	</main>

	{{-- Show/hide buttons for password fields --}}
	<script>
		document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
			button.addEventListener('click', function () {
				var input = document.getElementById(button.getAttribute('data-toggle-password'));
				var icon = button.querySelector('i');
				var show = input.type === 'password';

				input.type = show ? 'text' : 'password';
				icon.classList.toggle('fa-eye', !show);
				icon.classList.toggle('fa-eye-slash', show);
			});
		});
	</script>
</body>

</html>
