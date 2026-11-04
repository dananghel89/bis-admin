<aside id="left-panel" class="left-panel">
	<nav class="navbar navbar-expand-sm navbar-default">
		<div class="navbar-header">
			<a class="navbar-brand" href="{{ route('front') }}">{{ get_option('site_title') }}</a>
		</div>

		<div id="main-menu" class="main-menu">
			<ul class="nav navbar-nav">
				<li>
					<a href="{{ route('dashboard') }}">
						<i class="menu-icon fa fa-dashboard"></i> Dashboard
					</a>
				</li>

				{{-- Messages is not built yet (docs/finish-project-plan.md, Milestone 4): menu hidden until then.
				@can ( 'accessMessages' )
					<li class="menu-item-has-children dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="menu-icon fa fa-envelope-o"></i> Messages
						</a>

						<ul class="sub-menu children dropdown-menu">
							<li>
								<i class="fa fa-angle-right"></i>
								<a href="{{ route('messages') }}">Inbox</a>
							</li>

							<li>
								<i class="fa fa-angle-right"></i>
								<a href="{{ route('messagesSent') }}">Sent Messages</a>
							</li>

							<li>
								<i class="fa fa-angle-right"></i>
								<a href="{{ route('messageSend') }}">Send Message</a>
							</li>
						</ul>
					</li>
				@endcan
				--}}

				@can ( 'viewPosts' )
					<h3 class="menu-title">Content</h3>

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-info"></i> Infos
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('infos') }}">All Infos</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('infoAdd') }}">Add Info</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('infos') }}">
								<i class="menu-icon fa fa-info"></i> Infos
							</a>
						</li>
					@endcan

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-exclamation"></i> Alerts
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('alerts') }}">All Alerts</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('alertAdd') }}">Add Alert</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('alerts') }}">
								<i class="menu-icon fa fa-exclamation"></i> Alerts
							</a>
						</li>
					@endcan

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-compass"></i> Plans
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('plans') }}">All Plans</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('planAdd') }}">Add Plan</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('plans') }}">
								<i class="menu-icon fa fa-compass"></i> Plans
							</a>
						</li>
					@endcan

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-file-text-o"></i> Licenses
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('licenses') }}">All Licenses</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('licenseAdd') }}">Add License</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('licenses') }}">
								<i class="menu-icon fa fa-file-text-o"></i> Licenses
							</a>
						</li>
					@endcan
				@endcan
			
				@can ( 'editOptions' )
					<h3 class="menu-title">Administration</h3>

					<li>
						<a href="{{ route('options') }}">
							<i class="menu-icon fa fa-wrench"></i> Options
						</a>
					</li>

					@can ( 'addUsers' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-user"></i> Users
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('users') }}">All Users</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('userAdd') }}">Add User</a>
								</li>
							</ul>
						</li>
					@endcan
				@endcan
			</ul>
		</div>
	</nav>
</aside>

<div id="right-panel" class="right-panel">
	<header id="header" class="header">
		<div class="header-menu pl-1 pr-3">
			<div class="row">			
				<div class="col-7">
					<div class="page-header">
						<div class="page-title">
							<h1>@yield('title')</h1>
						</div>
					</div>
				</div>

				<div class="col-5">
					<div class="user-area dropdown float-right">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<img class="user-avatar rounded-circle mt-2" src="{{ get_gravatar( Auth::user()->email ) }}" alt="User Avatar" width="32" height="32">
						</a>

						<div class="user-menu dropdown-menu">
							@can ( 'editProfile' )
								<a class="nav-link" href="{{ route('settings') }}">
									<i class="fa fa-cog"></i> Settings
								</a>
							@endcan

							<a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								<i class="fa fa-power-off"></i> Log Out
							</a>

							<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
								{{ csrf_field() }}
							</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</header>

	<div class="content mt-4">
