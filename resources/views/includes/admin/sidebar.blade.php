<nav class="sidebar bg-dark text-white position-fixed d-flex flex-column" id="sidebar">
	<div class="sidebar-brand d-flex align-items-center justify-content-center py-3 border-bottom border-secondary">
		<a href="{{ route('dashboard') }}" class="text-white text-decoration-none fw-bold">
			<span class="logo-full fs-4">{{ config('project.site_title') }}</span>
			<span class="logo-mini fs-5">{{ mb_substr(config('project.site_title'), 0, 1) }}</span>
		</a>
	</div>

	<div class="sidebar-nav flex-grow-1 overflow-auto py-3">
		<ul class="nav flex-column">
			<li class="nav-item">
				<a class="nav-link" href="{{ route('dashboard') }}" data-page="dashboard" title="Dashboard">
					<i class="fas fa-tachometer-alt"></i>
					<span class="nav-text ms-2">Dashboard</span>
				</a>
			</li>

			@can ( 'viewPosts' )
				<li class="nav-item">
					<a class="nav-link" href="{{ route('alerts') }}" data-page="alerts" title="Alerts">
						<i class="fas fa-inbox"></i>
						<span class="nav-text ms-2">Alerts</span>
					</a>
				</li>

				<li class="nav-header mt-3 mb-2">
					<span class="small fw-bold text-uppercase text-light opacity-75">Properties</span>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="{{ route('infos') }}" data-page="infos" title="Infos">
						<i class="fas fa-info-circle"></i>
						<span class="nav-text ms-2">Infos</span>
					</a>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="{{ route('plans') }}" data-page="plans" title="Plans">
						<i class="fas fa-compass"></i>
						<span class="nav-text ms-2">Plans</span>
					</a>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="{{ route('licenses') }}" data-page="licenses" title="Licenses">
						<i class="far fa-file-alt"></i>
						<span class="nav-text ms-2">Licenses</span>
					</a>
				</li>
			@endcan

			@can ( 'viewUsers' )
				<li class="nav-header mt-3 mb-2">
					<span class="small fw-bold text-uppercase text-light opacity-75">Admin</span>
				</li>

				<li class="nav-item">
					<a class="nav-link" href="{{ route('users') }}" data-page="users" title="Users">
						<i class="fas fa-user"></i>
						<span class="nav-text ms-2">Users</span>
					</a>
				</li>
			@endcan
		</ul>
	</div>
</nav>
