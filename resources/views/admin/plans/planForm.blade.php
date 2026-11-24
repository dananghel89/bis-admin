@extends('layouts.admin')

@section('title', $plan->exists ? 'Edit Plan' : 'Upload Plan')
@section('page', 'plans')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('plans') }}">Plans</a></li>
	<li class="breadcrumb-item active" aria-current="page">{{ $plan->exists ? $plan->title : 'Upload' }}</li>
@endsection

@section('content')

	<div class="row">
		<div class="col-xl-8">
			<div class="card">
				<div class="card-header"><strong class="card-title">Plan</strong></div>

				<div class="card-body">
					<x-form.ajax :action="$plan->exists ? route('planEditSave', $plan) : route('planAddSave')" :method="$plan->exists ? 'PATCH' : 'POST'" files>
						<x-form.select name="property_id" label="Property" :options="$properties" :value="$plan->property_id" placeholder="Choose a property" required />
						<x-form.input name="title" label="Title" :value="$plan->title" required maxlength="255" placeholder="e.g. Ground floor" />
						<x-form.textarea name="description" label="Description" :value="$plan->description" rows="3" maxlength="2000" />

						<x-form.file name="file" label="{{ $plan->exists ? 'Replace the PDF' : 'PDF' }}" accept="application/pdf,.pdf" :required="! $plan->exists"
							:help="($plan->exists ? 'Now: '.$plan->file_name.' ('.$plan->fileSizeForHumans().'). Leave empty to keep it. ' : '').'PDF only, up to '.App\Support\PdfUpload::maxForHumans().'.'" />

						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $plan->exists ? 'Save Changes' : 'Upload' }}</button>
							<a href="{{ $plan->property_id ? route('infoView', $plan->property_id) : route('plans') }}" class="btn btn-outline-secondary">Cancel</a>
						</div>
					</x-form.ajax>
				</div>
			</div>
		</div>
	</div>

@endsection
