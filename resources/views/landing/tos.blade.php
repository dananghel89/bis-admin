@extends('layouts.landing')

{{-- page title --}}

@section('title', 'Terms of Service')

{{-- content section --}}

@section('content')

	<section class="tos" id="tos">
		<div class="container">
			<h2 class="text-center">Terms of Service</h2>

			<div class="row">
				<div class="col-lg-8 offset-lg-2">
					<p>These terms apply to everyone who uses {{ config('project.site_title') }} (Building Information System). By logging in, you agree to them.</p>

					<h3>1. The service</h3>
					<p>{{ config('project.site_title') }} is a private web application where our staff keep the information, plans and licenses of properties, and where the architects who work on those properties can read them, download them and report the status of their work.</p>

					<h3>2. Accounts</h3>
					<p>There is no public sign-up. Our staff create every account and choose its role. Keep your password to yourself, change it from your settings if you think someone else knows it, and tell us at once if your account is used without your permission. We may suspend or delete an account that is misused or no longer needed.</p>

					<h3>3. Documents are confidential</h3>
					<p>The information, plans and licenses you can see are shared with you only for your work on those properties. Don't share, publish or sell them, or use them for anything else, without permission from our staff. You can only see the properties our staff make available to you.</p>

					<h3>4. Acceptable use</h3>
					<p>Don't try to open pages, properties or files you weren't given access to, don't upload or send harmful files or content, and don't use the service in a way that could damage it or disturb other users.</p>

					<h3>5. Mailbox</h3>
					<p>The mailbox connects to an email account that belongs to you. Its settings and password are stored encrypted and are used only to read and send your email from the dashboard. You are responsible for the messages you send.</p>

					<h3>6. Availability</h3>
					<p>We work to keep the service available and the documents correct, but we can't promise that it will always be online or free of errors. Keep your own copy of any document you can't afford to lose.</p>

					<h3>7. Changes</h3>
					<p>We may update these terms. The current version is always on this page, and using the service after a change means you accept it.</p>

					<h3>8. Contact</h3>
					<p>Questions about these terms? Write to <a href="mailto:{{ config('project.contact_email') }}">{{ config('project.contact_email') }}</a> or use the <a href="{{ url('/#contact') }}">contact form</a>.</p>
				</div>
			</div>
		</div>
	</section>

@endsection
