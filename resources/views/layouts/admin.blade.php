<!DOCTYPE html>

<html lang="{{ app()->getLocale() }}" data-bs-theme="light">

<head>
	@include('includes.admin.head')
</head>

<body class="sufee-dashboard" data-page="@yield('page')">
	<div class="d-flex min-vh-100">
		@include('includes.admin.sidebar')

		<div class="main-content flex-grow-1">
			@include('includes.admin.header')

			{{-- The theme's breadcrumb, rendered here instead of by its script. --}}
			<div class="breadcrumb-section py-3">
				<div class="container-fluid">
					<div class="row align-items-center">
						<div class="col-md-6">
							<h1 class="h4 mb-0">@yield('title')</h1>
						</div>

						<div class="col-md-6">
							<nav aria-label="breadcrumb" class="float-md-end">
								<ol class="breadcrumb mb-0">
									<li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
									{{-- A page can list its own parent links in @section('breadcrumb'). --}}
									@hasSection('breadcrumb')
										@yield('breadcrumb')
									@elseif ( ! request()->routeIs('dashboard') )
										<li class="breadcrumb-item active" aria-current="page">@yield('title')</li>
									@endif
								</ol>
							</nav>
						</div>
					</div>
				</div>
			</div>

			<main class="content-area p-4">
				<div class="container-fluid">
					@include('includes.admin.alerts')

					@yield('content')
				</div>
			</main>
		</div>
	</div>

	@stack('scripts')
</body>

</html>
