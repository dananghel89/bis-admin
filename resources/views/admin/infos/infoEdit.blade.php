@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Edit Info')

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

	<div class="card_form">
		<div class="card">
			<form method="POST" action="{{ route('infoEditSave', $info['id']) }}" class="form-horizontal">
				{{ method_field('PATCH') }}
				{{ csrf_field() }}

				<div class="card-body card-block">
					<div class="row form-group">
						<div class="col-3">
							<label for="status" class=" form-control-label">Status</label>
						</div>

						<div class="col-9">
							<select name="status" id="status" class="form-control">
								@php
									$status_val = !empty(old('status')) ? old('status') : $info['status'];
								@endphp

								@foreach ( $statuses as $statusKey => $statusValue )
									<option value="{{ $statusKey }}" {{ (collect($status_val)->contains($statusKey)) ? 'selected' : '' }}>{{ $statusValue }}</option>
								@endforeach
							</select>
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="title" class="form-control-label">Title</label>
						</div>

						<div class="col-9">
							<input type="text" id="title" name="title" placeholder="Title" class="form-control" value="{{ !empty(old('title')) ? old('title') : $info['title'] }}">
						</div>
					</div>

					<div class="row form-group">
						<div class="col-3">
							<label for="content" class="form-control-label">Content</label>
						</div>

						<div class="col-9">
							<textarea name="content" id="content" rows="10" placeholder="Content" class="form-control">{{ !empty(old('content')) ? old('content') : $info['content'] }}</textarea>
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
							<button type="submit" class="btn btn-primary btn-sm mr-2">
								<i class="fa fa-dot-circle-o"></i> Update
							</button>

							<a href="{{ route('infoView', $info['id']) }}" class="btn btn-success btn-sm mr-2">
								<i class="fa fa-search"></i> View
							</a>

							@can ('deletePosts')
								<a class="btn btn-danger btn-sm" href="{{ route('infoDelete', $info['id']) }}" onclick="event.preventDefault(); document.getElementById('delete-info').submit();">
									<i class="fa fa-ban"></i> Delete
								</a>
							@endcan
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
		
			@can ('deletePosts')
				<form id="delete-info" action="{{ route('infoDelete', $info['id']) }}" method="POST" class="d-none">
					{{ method_field('DELETE') }}
					{{ csrf_field() }}
				</form>
			@endcan
		</div>
	</div>

@endsection
