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

				{{-- The theme's search toggle and form (its script shows and hides them): searches properties --}}
				@can ( 'viewProperties' )
					<button class="btn btn-link text-secondary p-2 me-2" id="searchToggle" type="button" aria-label="Search properties">
						<i class="fas fa-search"></i>
					</button>

					<form class="search-form d-none flex-grow-1" action="{{ route('infos') }}" method="GET" role="search" style="max-width: 400px">
						<div class="input-group">
							<input type="search" name="search" class="form-control" placeholder="Search properties..." aria-label="Search properties">
							<button class="btn btn-outline-secondary" type="submit"><i class="fas fa-search"></i></button>
							<button class="btn btn-outline-secondary" type="button" id="searchClose" aria-label="Close search"><i class="fas fa-times"></i></button>
						</div>
					</form>
				@endcan
			</div>

			<div class="d-flex align-items-center">
				<button type="button" class="btn btn-link text-secondary p-2" data-theme-toggle title="Toggle theme" aria-label="Toggle dark mode">
					<i class="fa fa-moon"></i>
				</button>

				@can ( 'useMailbox' )
					<a href="{{ route('alerts') }}" class="btn btn-link text-secondary p-2" title="Alerts" aria-label="Alerts">
						<i class="fas fa-envelope"></i>
					</a>
				@endcan

				{{-- The theme's notifications dropdown: for staff, unread contacts and licenses to check --}}
				@can ( 'readContacts' )
					@php
						$notices = array_filter([
							'contacts' => App\Models\Contact::whereNull('read_at')->count(),
							'licenses' => App\Models\License::needsAttention()->count(),
						]);
					@endphp
					<div class="dropdown">
						<button class="btn btn-link text-secondary position-relative p-2" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
							<i class="fas fa-bell"></i>
							@if ( $notices )
								<span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="font-size: 10px">{{ array_sum($notices) }}</span>
							@endif
						</button>
						<ul class="dropdown-menu dropdown-menu-end shadow border-0" style="width: 300px; max-width: 90vw">
							<li><h6 class="dropdown-header bg-body-tertiary fw-bold py-3 px-3">Notifications</h6></li>
							@if ( isset($notices['contacts']) )
								<li>
									<a class="dropdown-item py-2 px-3" href="{{ route('contacts', ['unread' => 1]) }}">
										<i class="fas fa-at text-danger me-2"></i> {{ trans_choice(':count unread contact message|:count unread contact messages', $notices['contacts']) }}
									</a>
								</li>
							@endif
							@if ( isset($notices['licenses']) )
								<li>
									<a class="dropdown-item py-2 px-3" href="{{ route('licenses', ['state' => 'attention']) }}">
										<i class="fas fa-file-alt text-warning me-2"></i> {{ trans_choice(':count license expired or expiring|:count licenses expired or expiring', $notices['licenses']) }}
									</a>
								</li>
							@endif
							@unless ( $notices )
								<li><span class="dropdown-item-text small text-muted px-3">Nothing new.</span></li>
							@endunless
						</ul>
					</div>
				@endcan

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
