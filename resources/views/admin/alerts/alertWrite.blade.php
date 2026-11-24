@extends('layouts.admin')

@section('title', $draft['reply_to'] ? 'Reply' : 'Write an Email')
@section('page', 'alerts')

@section('breadcrumb')
	<li class="breadcrumb-item"><a href="{{ route('alerts') }}">Alerts</a></li>
	<li class="breadcrumb-item active" aria-current="page">Write</li>
@endsection

@section('content')

	<div class="row">
		<div class="col-xl-9">
			<div class="card">
				<div class="card-header">
					<span class="small text-muted">From</span>
					<strong>{{ Auth::user()->name }} &lt;{{ $mailbox->account()->email }}&gt;</strong>
				</div>

				<div class="card-body">
					<x-form.ajax :action="route('alertSend')">
						<input type="hidden" name="reply_to" value="{{ old('reply_to', $draft['reply_to']) }}">

						<x-form.input name="to" label="To" :value="$draft['to']" required help="Separate several addresses with commas." />
						<x-form.input name="cc" label="Cc" :value="$draft['cc']" />
						<x-form.input name="subject" label="Subject" :value="$draft['subject']" required maxlength="255" />
						<x-form.textarea name="body" label="Message" :value="$draft['body']" rows="14" required />

						<div class="d-flex gap-2">
							<button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Send</button>
							<a href="{{ route('alerts') }}" class="btn btn-outline-secondary">Cancel</a>
						</div>
					</x-form.ajax>
				</div>
			</div>
		</div>
	</div>

@endsection
