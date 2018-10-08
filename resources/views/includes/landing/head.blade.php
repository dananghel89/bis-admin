<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">

<title>{{ get_option('site_title') }} - @yield('title') | {{ get_option('tagline') }}</title>

<link rel="shortcut icon" href="{{ asset('landing/img/favicon.png') }}">

<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Raleway:400,500,700|Roboto:400,900">
<link rel="stylesheet" href="{{ asset('landing/lib/bootstrap/css/bootstrap.min.css') }}">
<link rel="stylesheet" href="{{ asset('landing/lib/font-awesome/css/font-awesome.min.css') }}">
<link rel="stylesheet" href="{{ asset('landing/css/style.css') }}">

<style type="text/css">
	.social-nav .btn {
		padding: 6px 30px;
		font-size: 1em !important;
		color: #FFF;
		border-color: #FFF;
	}

	.social-nav .btn:hover,
	.social-nav .btn:focus {
		color: #FFF !important;
		background-color: #4db6ac !important;
	}

	.block-parallax {
		height: 260px;
	}

	.contactForm textarea {
		resize: none;
	}

	.info .fa {
		width: 32px;
		height: auto;
		text-align: center;
		font-size: 26px !important;
	}
</style>
