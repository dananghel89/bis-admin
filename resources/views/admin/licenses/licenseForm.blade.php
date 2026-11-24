@extends('layouts.admin')

@section('title', $license->exists ? 'Edit License' : 'Upload License')
@section('page', 'licenses')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('licenses') }}">Licenses</a></li>
	<li class="breadcrumb-item active" aria-current="page">{{ $license->exists ? $license->title : 'Upload' }}</li>
@endsection

@section('content')

	<div class="row">
		<div class="col-xl-8">
			<div class="card">
				<div class="card-header"><strong class="card-title">License</strong></div>

				<div class="card-body">
					<x-form.ajax :action="$license->exists ? route('licenseEditSave', $license) : route('licenseAddSave')" :method="$license->exists ? 'PATCH' : 'POST'" files>
						<x-form.select name="property_id" label="Property" :options="$properties" :value="$license->property_id" placeholder="Choose a property" required />
						<x-form.input name="title" label="Title" :value="$license->title" required maxlength="255" placeholder="e.g. Building permit" />
						<x-form.input name="number" label="Number" :value="$license->number" maxlength="100" />

						<div class="row">
							<div class="col-sm-6">
								<x-form.input name="issued_at" label="Issued" type="date" :value="$license->issued_at?->format('Y-m-d')" />
							</div>
							<div class="col-sm-6">
								<x-form.input name="expires_at" label="Expires" type="date" :value="$license->expires_at?->format('Y-m-d')"
									help="Licenses expiring within {{ App\Models\License::EXPIRING_DAYS }} days are flagged." />
							</div>
						</div>

						<x-form.file name="file" label="{{ $license->exists ? 'Replace the PDF' : 'PDF' }}" accept="application/pdf,.pdf" :required="! $license->exists"
							:help="($license->exists ? 'Now: '.$license->file_name.' ('.$license->fileSizeForHumans().'). Leave empty to keep it. ' : '').'PDF only, up to '.App\Support\PdfUpload::maxForHumans().'.'" />

						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> {{ $license->exists ? 'Save Changes' : 'Upload' }}</button>
							<a href="{{ $license->property_id ? route('infoView', $license->property_id) : route('licenses') }}" class="btn btn-outline-secondary">Cancel</a>
						</div>
					</x-form.ajax>
				</div>
			</div>
		</div>
	</div>

@endsection
