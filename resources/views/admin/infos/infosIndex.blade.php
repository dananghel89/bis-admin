@extends('layouts.admin')

{{-- page title --}}

@section('title', 'Todas Las Informaciones')

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

	<div class="card_table mb-4">
		<table class="table table-striped">
			<thead>
				<tr>
					<th scope="col" class="col_1">#</th>
					<th scope="col" class="col_2">Title</th>
					<th scope="col" class="col_3">Author</th>
					<th scope="col" class="col_4">Date</th>
				</tr>
			</thead>
			
			<tbody>
				@php
					$counter = 1;
					$public_infos = [];
					$location = 'infoEdit';

					foreach ( $infos as $info ) {
						if ( $info['status'] == 'public' ) {
							$public_infos[] = $info;
						}
					}
				@endphp

				@cannot ('editPosts')
					@php
						$infos = $public_infos;
						$location = 'infoView';
					@endphp
				@endcan

				@foreach ( $infos as $info )
					<tr>
						<th scope="row">{{ $counter }}</th>
						<td><a href="{{ route($location, $info['id']) }}" class="btn-link">{{ $info['title'] }}</a></td>
						<td>{{ optional($user::find($info['author']))->name ?? '-' }}</td>
						<td>{{ date('Y/m/d', strtotime($info['created_at'])) }}</td>
					</tr>

					@php
						$counter++;
					@endphp
				@endforeach
			</tbody>
		</table>
	</div>

	<div class="card">
		<div class="card-footer border-top-0">
			<div class="row">
				<div class="col-9">
					@can ('addPosts')
						<a href="{{ route('infoAdd') }}" class="btn btn-success btn-sm mr-2">
							<i class="fa fa-plus"></i> Add Info
						</a>
					@endcan
				</div>

				<div class="col-3">
					<p class="m-0 pt-1 text-right">{{ count($infos) }} items</p>
				</div>
			</div>
		</div>
	</div>

@endsection
