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
				<p class="tagline">Property information, plans and licenses for architects, in one place.</p>

				@auth
					<a class="btn btn-full" href="{{ route('dashboard') }}">Dashboard</a>
				@else
					<a class="btn btn-full" href="{{ route('login') }}">Log In</a>
				@endauth
			</div>
		</div>
	</section>

@endsection

{{-- content section --}}

@section('content')

	<section class="about" id="about">
		<div class="container text-center">
			<h2>About {{ config('project.site_title') }}</h2>

			<p>{{ config('project.site_title') }} (Building Information System) keeps the documentation of every property in one place. Our staff add the information, plans and licenses of each property, and the architects who work on it open them, download the PDF files and report the progress of their work. There is no public sign-up: staff create every account, so each document stays with the people who need it.</p>

			<div class="row stats-row">
				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">1</span> Property <br> Records
					</div>
				</div>

				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">2</span> Plans on <br> a Map
					</div>
				</div>

				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">3</span> Licenses <br> &amp; Dates
					</div>
				</div>

				<div class="stats-col text-center col-md-3 col-sm-6">
					<div class="circle">
						<span class="stats-no">4</span> Built-in <br> Mailbox
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
			<h2>Welcome to {{ config('project.site_title') }}</h2>
			<p class="mb-0">Everything an architect needs to know about a property, one login away.</p>
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
								<span class="fa fa-home"></span>
							</div>
						</div>

						<div>
							<h3>Property Information</h3>
							<p>The address, description and location of every property, kept up to date by our staff and ready whenever you need them.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-map-marker"></span>
							</div>
						</div>

						<div>
							<h3>Plans on a Map</h3>
							<p>See where each property is on the map, then open its plans and download them as PDF files.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-file-text-o"></span>
							</div>
						</div>

						<div>
							<h3>Licenses &amp; Expiry Dates</h3>
							<p>Every license with its number, issue date and expiry date, so a license that is about to expire never goes unnoticed.</p>
						</div>
					</div>
				</div>
			</div>

			<div class="row">
				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-tasks"></span>
							</div>
						</div>

						<div>
							<h3>Work Status</h3>
							<p>Architects mark their work on a property as pending, in progress or done, with a short note. Staff see every change.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-envelope"></span>
							</div>
						</div>

						<div>
							<h3>Built-in Mailbox</h3>
							<p>Send and receive email from your own address without leaving the dashboard.</p>
						</div>
					</div>
				</div>

				<div class="feature-col col-lg-4 col-xs-12">
					<div class="card card-block text-center">
						<div>
							<div class="feature-icon">
								<span class="fa fa-lock"></span>
							</div>
						</div>

						<div>
							<h3>Private Access</h3>
							<p>No public sign-up. Our staff create each account, and architects only see the properties they work on.</p>
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
						@if ( config('project.contact_address') )
							<div>
								<i class="fa fa-map-marker"></i>
								<p>{!! nl2br(e(config('project.contact_address'))) !!}</p>
							</div>
						@endif

						<div>
							<i class="fa fa-envelope"></i>
							<p><a href="mailto:{{ config('project.contact_email') }}">{{ config('project.contact_email') }}</a></p>
						</div>

						@if ( config('project.contact_phone') )
							<div>
								<i class="fa fa-phone"></i>
								<p><a href="tel:{{ preg_replace('/[^0-9+]/', '', config('project.contact_phone')) }}">{{ config('project.contact_phone') }}</a></p>
							</div>
						@endif
					</div>
				</div>

				<div class="col-lg-5 col-md-8">
					<div class="form">						
						{{-- Filled in by the server after a normal post, or by js/ajax-form.js after an ajax one. --}}
						<div id="sendmessage" class="{{ session('contact_status') ? 'show' : '' }}" role="status">{{ session('contact_status') }}</div>
						<div id="errormessage" class="{{ $errors->has('contact') ? 'show' : '' }}" role="alert">{{ $errors->first('contact') }}</div>

						<form role="form" class="contactForm" method="POST" action="{{ route('contact') }}" novalidate
							data-ajax-form data-success="#sendmessage" data-error="#errormessage" data-error-class="show">
							@csrf

							{{-- Honeypot: hidden from people, bots fill it in. --}}
							<div class="hp-field" aria-hidden="true">
								<label for="website">Leave this field empty</label>
								<input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
							</div>

							@foreach ( ['name' => 'Your Name', 'email' => 'Your Email', 'subject' => 'Subject'] as $field => $placeholder )
								<div class="form-group">
									<input type="{{ $field === 'email' ? 'email' : 'text' }}" name="{{ $field }}" class="form-control" id="{{ $field }}" placeholder="{{ $placeholder }}" value="{{ old($field) }}" required>
									<div class="validation {{ $errors->has($field) ? 'show' : '' }}" data-error-for="{{ $field }}">{{ $errors->first($field) }}</div>
								</div>
							@endforeach

							<div class="form-group">
								<textarea class="form-control" name="message" id="message" rows="5" placeholder="Message" required>{{ old('message') }}</textarea>
								<div class="validation {{ $errors->has('message') ? 'show' : '' }}" data-error-for="message">{{ $errors->first('message') }}</div>
							</div>

							<div class="text-center">
								<button type="submit">Send Message</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</section>

@endsection

@push('scripts')
	<script src="{{ asset('js/ajax-form.js') }}"></script>
@endpush
