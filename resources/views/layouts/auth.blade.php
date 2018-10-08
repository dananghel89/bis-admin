<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" class="bis_auth">

<head>
	@include('includes.auth.head')
</head>

<body>
	@include('includes.auth.header')
	
	@yield('content')

	@include('includes.auth.footer')
</body>

</html>
