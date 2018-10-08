<header id="header">
	<div class="container">
		<div id="logo" class="pull-left">
			<a href="{{ route('front') }}">
				<img src="{{ asset('landing/img/logo-nav.png') }}" width="163" height="51" alt="Logo">
			</a>
		</div>

		<nav id="nav-menu-container">
			<ul class="nav-menu">
				<li><a href="{{ url('/#about') }}">About Us</a></li>
				<li><a href="{{ url('/#features') }}">Features</a></li>
				<li><a href="{{ url('/#contact') }}">Contact Us</a></li>
			</ul>
		</nav>

		@if ( Route::has('login') )
			<nav class="nav social-nav pull-right d-none d-lg-inline">
				@if ( Auth::check() )
					<a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">Access Dashboard</a>
				@else
					<a href="{{ route('login') }}" class="btn btn-outline-secondary">Dashboard Log In</a>
				@endif
			</nav>
		@endif
	</div>
</header>
