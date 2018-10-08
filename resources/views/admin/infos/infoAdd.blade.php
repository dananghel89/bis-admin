@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Añadir Información')

{{-- content section --}}

@section('content')

	<div class="card_form">
		<div class="card">
			<form method="POST" action="{{ route('infoAddSave') }}" class="form-horizontal">
				{{ csrf_field() }}

				<div class="card-body card-block">
					<div class="row form-group">
						<div class="col-3">
							<label for="status" class=" form-control-label">Status</label>
						</div>

						<div class="col-9">
							<select name="status" id="status" class="form-control">
								@foreach ( $statuses as $statusKey => $statusValue )
									<option value="{{ $statusKey }}" {{ (collect(old('status'))->contains($statusKey)) ? 'selected' : '' }}>{{ $statusValue }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="title" class="form-control-label">Title</label>
						</div>

						<div class="col-9">
							<input type="text" id="title" name="title" placeholder="Title" class="form-control" value="{{ old('title') }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="content" class="form-control-label">Content</label>
						</div>

						<div class="col-9">
							<textarea name="content" id="content" rows="10" placeholder="Content" class="form-control">{{ old('content') }}</textarea>
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
					<div class="row">
						<div class="col-9">
							<button type="submit" class="btn btn-primary btn-sm">
								<i class="fa fa-dot-circle-o"></i> Publish
							</button>
						</div>

						<div class="col-3">
							<div class="text-right">
								<a href="{{ route('infos') }}" class="btn btn-secondary btn-sm">
									<i class="fa fa-angle-left"></i> Back to Infos
								</a>
							</div>
						</div>
					</div>
				</div>
			</form>
		</div>
	</div>

@endsection
