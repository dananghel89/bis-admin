<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" class="bis_admin">

<head>
	@include('includes.admin.head')
</head>

<body>
	@include('includes.admin.header')

	@yield('content')

	@include('includes.admin.footer')
</body>

</html>
