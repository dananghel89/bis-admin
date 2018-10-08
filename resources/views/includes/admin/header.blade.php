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

				@can ( 'accessMessages' )
					<li class="menu-item-has-children dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
							<i class="menu-icon fa fa-envelope-o"></i> Mensajes
						</a>

						<ul class="sub-menu children dropdown-menu">
							<li>
								<i class="fa fa-angle-right"></i>
								<a href="{{ route('messages') }}">Bandeja De Entrada <span class="badge badge-success">10</span></a>
							</li>

							<li>
								<i class="fa fa-angle-right"></i>
								<a href="{{ route('messagesSent') }}">Mensajes Enviados</a>
							</li>

							<li>
								<i class="fa fa-angle-right"></i>
								<a href="{{ route('messageSend') }}">Enviar Mensaje</a>
							</li>
						</ul>
					</li>
				@endcan

				@can ( 'viewPosts' )
					<h3 class="menu-title">Datos De Sistema</h3>

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-info"></i> Informaciones
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('infos') }}">Todas Las Informaciones</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('infoAdd') }}">Añadir Información</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('infos') }}">
								<i class="menu-icon fa fa-info"></i> Informaciones
							</a>
						</li>
					@endcan

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-exclamation"></i> Avisos
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('alerts') }}">Todos Los Avisos</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('alertAdd') }}">Añadir Aviso</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('alerts') }}">
								<i class="menu-icon fa fa-exclamation"></i> Avisos
								
								@can ( 'receiveAlerts' )
									<span class="badge badge-success">10</span>
								@endcan
							</a>
						</li>
					@endcan

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-compass"></i> Planos
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('plans') }}">Todos Los Planos</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('planAdd') }}">Añadir Plan</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('plans') }}">
								<i class="menu-icon fa fa-compass"></i> Planos
							</a>
						</li>
					@endcan

					@can ( 'addPosts' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-file-text-o"></i> Licencias
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('licenses') }}">Todas Las Licencias</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('licenseAdd') }}">Añadir Licencia</a>
								</li>
							</ul>
						</li>
					@else
						<li>
							<a href="{{ route('licenses') }}">
								<i class="menu-icon fa fa-file-text-o"></i> Licencias
							</a>
						</li>
					@endcan
				@endcan
			
				@can ( 'editOptions' )
					<h3 class="menu-title">Administración</h3>

					<li>
						<a href="{{ route('options') }}">
							<i class="menu-icon fa fa-wrench"></i> Opciones
						</a>
					</li>

					@can ( 'addUsers' )
						<li class="menu-item-has-children dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="menu-icon fa fa-user"></i> Usuarios
							</a>

							<ul class="sub-menu children dropdown-menu">
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('users') }}">Todos Los Usuarios</a>
								</li>
								
								<li>
									<i class="fa fa-angle-right"></i>
									<a href="{{ route('userAdd') }}">Añadir Usuario</a>
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
							<img class="user-avatar rounded-circle mt-2" src="{{ get_gravatar( Auth::user()->email ) }}" alt="User Avatar" width="410" height="400">
						</a>

						<div class="user-menu dropdown-menu">
							@can ( 'editProfile' )
								<a class="nav-link" href="{{ route('settings') }}">
									<i class="fa fa-cog"></i> Ajustes
								</a>
							@endcan

							<a class="nav-link" href="{{ route('logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
								<i class="fa fa-power-off"></i> Cerrar Sesión
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
