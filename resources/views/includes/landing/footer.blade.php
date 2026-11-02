<footer class="site-footer">
	<div class="bottom">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-xs-12 text-lg-left text-center">
					<p class="copyright-text">© {{ date('Y') }} {{ get_option('site_title') }} - All Rights Reserved</p>
					<div class="credits">
						<!--
							All the links in the footer should remain intact.
							You can delete the links only if you purchased the pro version.
							Licensing information: https://bootstrapmade.com/license/
							Purchase the pro version with working PHP/AJAX contact form: https://bootstrapmade.com/buy/?theme=Bell
						-->
						<a href="https://bootstrapmade.com/">Bootstrap Templates</a> by BootstrapMade
					</div>
				</div>

				<div class="col-lg-6 col-xs-12 text-lg-right text-center">
					<ul class="list-inline">
						<li class="list-inline-item">
							<a href="{{ route('front') }}">Home</a>
						</li>
						<li class="list-inline-item">
							<a href="{{ route('tos') }}">Terms of Service</a>
						</li>
						<li class="list-inline-item">
							<a href="{{ url('/#contact') }}">Contact</a>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</div>
</footer>

<a class="scrolltop" href="#"><span class="fa fa-angle-up"></span></a>

<script src="{{ asset('landing/lib/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('landing/lib/jquery/jquery-migrate.min.js') }}"></script>
<script src="{{ asset('landing/lib/superfish/hoverIntent.js') }}"></script>
<script src="{{ asset('landing/lib/superfish/superfish.min.js') }}"></script>
<script src="{{ asset('landing/lib/stellar/stellar.min.js') }}"></script>
<script src="{{ asset('landing/lib/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('landing/lib/easing/easing.js') }}"></script>
<script src="{{ asset('landing/lib/stickyjs/sticky.js') }}"></script>
<script src="{{ asset('landing/lib/parallax/parallax.js') }}"></script>
<script src="{{ asset('landing/js/custom.js') }}"></script>
