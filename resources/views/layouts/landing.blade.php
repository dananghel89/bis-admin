<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}">

<head>
	@include('includes.landing.head')
</head>

<body>
	@yield('hero')

	@include('includes.landing.header')

	@yield('content')

	@include('includes.landing.footer')
</body>

</html>
