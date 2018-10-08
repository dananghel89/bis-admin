@extends('layouts.landing')

{{-- page title --}}

@section('title', 'Building Information System')

{{-- hero section --}}

@section('hero')
	<section class="hero">
		<div class="container text-center">
			<div class="row">
				<div class="col-md-12">
					<a class="hero-brand" href="{{ route('front') }}">
						<img src="{{ asset('landing/img/logo.png') }}" width="163" height="51" alt="Logo">
					</a>
				</div>
			</div>

			<div class="col-md-12">
				<h1>Building Information System</h1>
				<p class="tagline">The architecture web application for documentation, plans & licenses.</p>

				@if ( Route::has('login') && get_option('membership') )
					@if ( !Auth::check() )
						<a class="btn btn-full" href="{{ route('register') }}">Get Started Now</a>
					@endif
				@endif
			</div>
		</div>
	</section>

@endsection

{{-- content section --}}

@section('content')

	<section class="about" id="about">
		<div class="container text-center">
			<h2>About {{ get_option('site_title') }}</h2>

			<p>Voluptua scripserit per ad, laudem viderer sit ex. Ex alia corrumpit voluptatibus usu, sed unum convenire id. Ut cum nisl moderatius, per nihil dicant commodo an. Eum tacimates erroribus ad. Atqui feugiat euripidis ea pri, sed veniam tacimates ex. Menandri temporibus an duo.</p>

			<div class="row stats-row">
				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">1</span> Easy. Fast. <br> Efficient.
					</div>
				</div>

				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">2</span> Direct <br> Messages
					</div>
				</div>

				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">3</span> Digital <br> Plans
					</div>
				</div>

				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">4</span> Optimize <br> Workflow
					</div>
				</div>
			</div>
		</div>
	</section>

	@php
		$parallax_cls = 'block bg-secondary block-pd-lg block-bg-overlay text-center block-parallax';
		$parallax_img = asset('landing/img/parallax-bg.jpg');
	@endphp

	<div class="{{ $parallax_cls }}" data-bg-img="{{ $parallax_img }}" data-settings='{"stellar-background-ratio": 0.6}' data-toggle="parallax-bg">
		<div class="container">
			<h2>Welcome to {{ get_option('site_title') }}</h2>
			<p class="mb-0">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Proin aliquam blandit velit vel eleifend.</p>
		</div>
	</div>

	<section class="features" id="features">
		<div class="container">
			<h2 class="text-center">Features</h2>

			<div class="row">
				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-wrench"></span>
							</div>
						</div>

						<div>
							<h3>Speciality Architect</h3>
							<p>Nam sodales eleifend posuere. Sed eget nisl non risus placerat ornare. Mauris sit amet tempor eros. Etiam commodo sem sed gravida egestas.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-home"></span>
							</div>
						</div>

						<div>
							<h3>Facility Management</h3>
							<p>Eque feugiat contentiones ei has. Id summo mundi explicari his, nec in maiorum scriptorem. Sed eget nisl non risus placerat ornare.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-search"></span>
							</div>
						</div>

						<div>
							<h3>External Expert</h3>
							<p>Id summo mundi explicari his, nec in maiorum scriptorem. Eque feugiat contentiones ei has. Mauris sit amet tempor eros. Etiam sed gravida egestas.</p>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-briefcase"></span>
							</div>
						</div>

						<div>
							<h3>General Contractor</h3>
							<p>Id summo mundi explicari his, nec in maiorum scriptorem. Eque feugiat contentiones ei has. Mauris sit amet tempor eros. Etiam sed gravida egestas.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-cog"></span>
							</div>
						</div>

						<div>
							<h3>Owner</h3>
							<p>Nam sodales eleifend posuere. Sed eget nisl non risus placerat ornare. Mauris sit amet tempor eros. Etiam commodo sem sed gravida egestas.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-info"></span>
							</div>
						</div>

						<div>
							<h3>Informations &amp; Plans</h3>
							<p>Eque feugiat contentiones ei has. Id summo mundi explicari his, nec in maiorum scriptorem. Sed eget nisl non risus placerat ornare.</p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section id="contact">
		<div class="container">
			<div class="row">
				<div class="col-md-12 text-center">
					<h2 class="section-title">Contact Us</h2>
				</div>
			</div>

			<div class="row justify-content-center">
				<div class="col-lg-3 col-md-4">
					<div class="info">
						<div>
							<i class="fa fa-map-marker"></i>
							<p>Carrer de Marià Cubí, <br> 178-184, 08021 Barcelona</p>
						</div>

						<div>
							<i class="fa fa-envelope"></i>
							<p>dev@dananghel.com</p>
						</div>

						<div>
							<i class="fa fa-phone"></i>
							<p>+34 935 666 222</p>
						</div>
					</div>
				</div>

				<div class="col-lg-5 col-md-8">
					<div class="form">						
						<form role="form" class="contactForm">
							<fieldset disabled>
								<div class="form-group">
									<input type="text" name="name" class="form-control" id="name" placeholder="Your Name">
								</div>
								<div class="form-group">
									<input type="email" class="form-control" name="email" id="email" placeholder="Your Email">
								</div>
								<div class="form-group">
									<input type="text" class="form-control" name="subject" id="subject" placeholder="Subject">
								</div>
								<div class="form-group">
									<textarea class="form-control" name="message" rows="5" placeholder="Message"></textarea>
								</div>
								<div class="text-center">
									<button type="submit">Send Message</button>
								</div>
							</fieldset>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

@endsection
