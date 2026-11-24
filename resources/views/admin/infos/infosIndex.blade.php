@extends('layouts.admin')

{{-- page title --}}

@section('title', 'All Infos')
@section('page', 'infos')

{{-- content section --}}

@section('content')


	<div class="card mb-4 card_table">
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
					// The controller already limits $infos to what this user may read (Info::visibleTo).
					$counter = 1;
					$location = Auth::user()->can('editPosts') ? 'infoEdit' : 'infoView';
				@endphp

				@foreach ( $infos as $info )
					<tr>
						<th scope="row">{{ $counter }}</th>
						<td><a href="{{ route($location, $info['id']) }}" class="btn-link">{{ $info['title'] }}</a></td>
						<td>{{ $info['author_user']['name'] ?? '-' }}</td>
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
						<a href="{{ route('infoAdd') }}" class="btn btn-success btn-sm me-2">
							<i class="fas fa-plus"></i> Add Info
						</a>
					@endcan
				</div>

				<div class="col-3">
					<p class="m-0 pt-1 text-end">{{ count($infos) }} items</p>
				</div>
			</div>
		</div>
	</div>

@endsection
