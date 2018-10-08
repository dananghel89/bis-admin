<footer class="site-footer">
	<div class="bottom">
		<div class="container">
			<div class="row">
				<div class="col-lg-6 col-xs-12 text-lg-left text-center">
					<p class="copyright-text">© {{ date('Y') }} {{ get_option('site_title') }} - All Rights Reserved</p>
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
