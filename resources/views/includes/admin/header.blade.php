<header class="header">
	<nav class="navbar navbar-expand-lg">
		<div class="container-fluid px-3">
			<div class="d-flex align-items-center flex-grow-1">
				{{-- Sidebar toggles (the theme's script wires them up) --}}
				<button class="btn rounded-circle p-0 d-flex align-items-center justify-content-center me-3 d-lg-none sidebar-toggle" id="sidebarToggleMobile" type="button" aria-label="Show the menu">
					<i class="fas fa-bars"></i>
				</button>

				<button class="btn rounded-circle p-0 d-none d-lg-flex align-items-center justify-content-center me-3 sidebar-toggle" id="sidebarToggleDesktop" type="button" aria-label="Collapse the menu">
					<i class="fas fa-bars"></i>
				</button>

				@stack('header-left')
			</div>

			<div class="d-flex align-items-center">
				<button type="button" class="btn btn-link text-secondary p-2" data-theme-toggle title="Toggle theme" aria-label="Toggle dark mode">
					<i class="fa fa-moon"></i>
				</button>

				@stack('header-right')

				<div class="dropdown">
					<button class="btn btn-link text-decoration-none p-2 d-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
						<img src="{{ get_gravatar(Auth::user()->email, 64) }}" alt="" class="rounded-circle me-2" width="32" height="32">
						<span class="text-body-emphasis d-none d-md-inline fw-semibold small">{{ Auth::user()->name }}</span>
						<i class="fas fa-angle-down text-secondary ms-1 small"></i>
					</button>

					<ul class="dropdown-menu dropdown-menu-end shadow border-0">
						<li>
							<span class="dropdown-item-text small text-muted">{{ Auth::user()->email }}</span>
						</li>
						<li><hr class="dropdown-divider"></li>
						@can ( 'editProfile' )
							<li>
								<a class="dropdown-item py-2 px-3" href="{{ route('settings') }}">
									<i class="fas fa-cog me-2"></i> Settings
								</a>
							</li>
						@endcan
						<li>
							<a class="dropdown-item py-2 px-3" href="{{ route('front') }}">
								<i class="fas fa-globe me-2"></i> Website
							</a>
						</li>
						<li><hr class="dropdown-divider"></li>
						<li>
							<form method="POST" action="{{ route('logout') }}">
								@csrf
								<button type="submit" class="dropdown-item py-2 px-3">
									<i class="fas fa-power-off me-2"></i> Log Out
								</button>
							</form>
						</li>
					</ul>
				</div>
			</div>
		</div>
	</nav>
</header>
