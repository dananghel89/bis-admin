@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Opciones')

{{-- content section --}}

@section('content')

	@if ( $message = Session::get('success') )
		<div class="sufee-alert alert with-close alert-success alert-dismissible fade show">
			<span>{{ $message }}</span>
			<button type="button" class="close" data-dismiss="alert" aria-label="Close">
				<span aria-hidden="true">&times;</span>
			</button>
		</div>
	@endif

	<div class="card_medium_form">
		<div class="card">
			<form method="POST" action="{{ route('optionsEdit') }}" class="form-horizontal">
				{{ method_field('PATCH') }}
				{{ csrf_field() }}

				<div class="card-body card-block">
					<div class="row form-group">
						<div class="col-3">
							<label for="site_title" class="form-control-label">Site Title</label>
						</div>

						<div class="col-9">
							<input type="text" id="site_title" name="site_title" placeholder="Site Title" class="form-control" value="{{ !empty(old('site_title')) ? old('site_title') : $site_title }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="tagline" class="form-control-label">Tagline</label>
						</div>

						<div class="col-9">
							<input type="text" id="tagline" name="tagline" placeholder="Tagline" class="form-control" value="{{ !empty(old('tagline')) ? old('tagline') : $tagline }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="email_address" class="form-control-label">Email Address</label>
						</div>

						<div class="col-9">
							<input type="email" id="email_address" name="email_address" placeholder="Email Address" class="form-control" value="{{ !empty(old('email_address')) ? old('email_address') : $email_address }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label class="form-control-label">Membership</label>
						</div>

						<div class="col-9">
							<div class="form-check">
								<div class="checkbox">
									<label for="membership" class="form-check-label">
										<input type="checkbox" id="membership" name="membership" class="form-check-input" value="1" {{ $membership ? 'checked' : '' }}> Anyone can register
									</label>
								</div>
							</div>
						</div>
					</div>

					@if ( count($errors) > 0 )
						<div class="row form-group mb-0">
							<div class="col-3">
								<label class="form-control-label">Validation</label>
							</div>

							<div class="col-9">
								<div class="alert alert-danger mb-0" role="alert">
									@foreach ( $errors->all() as $error )
										<p class="mb-0">{{ $error }}</p>
									@endforeach
								</div>
							</div>
						</div>
					@endif
				</div>

				<div class="card-footer">
					<button type="submit" class="btn btn-primary btn-sm">
						<i class="fa fa-dot-circle-o"></i> Save Changes
					</button>
				</div>
			</form>
		</div>
	</div>

@endsection
