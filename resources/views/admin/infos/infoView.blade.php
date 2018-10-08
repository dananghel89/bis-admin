@extends('layouts.admin')

{{-- page title --}}

@section('title', 'View Info')

{{-- content section --}}

@section('content')

	@php
		$can_view = true;
	@endphp

	@cannot ('editPosts')
		@php
			if ( $info['status'] == 'private' ) {
				$can_view = false;
			}
		@endphp
	@endcan

	@if ( $can_view )
		<div class="card_form">
			<div class="card">
				<div class="card-header">
					<span class="card-title">Title</span>
				</div>
				<div class="card-body">
					<p class="card-text">{{ $info['title'] }}</p>
				</div>
			</div>

			@if ( !is_null($info['content']) )
				<div class="card">
					<div class="card-header">
						<span class="card-title">Content</span>
					</div>
					<div class="card-body">
						<p class="card-text">{{ $info['content'] }}</p>
					</div>
				</div>
			@endif

			<div class="card">
				<div class="card-footer border-top-0">
					<div class="row">
						<div class="col-9">
							@can ('editPosts')
								<a href="{{ route('infoEdit', $info['id']) }}" class="btn btn-success btn-sm mr-2">
									<i class="fa fa-search"></i> Edit
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
			</div>
		</div>
	@else
		<div class="alert alert-danger" role="alert">
			You are not allowed to view info.
		</div>
	@endif

@endsection
