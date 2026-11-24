{{--
	Mail settings for Alerts. Used on Settings (your own) and on a user's edit page (staff).
	Needs: $account (MailAccount|null), $saveUrl, $testUrl, $deleteUrl.
--}}
@php($account = $account ?? null)

<div class="card mt-4">
	<div class="card-header d-flex justify-content-between align-items-center">
		<strong class="card-title">Mail Settings for Alerts</strong>
		@if ( $account )
			<span class="badge bg-success">Set up</span>
		@else
			<span class="badge bg-secondary">Not set up</span>
		@endif
	</div>

	<div class="card-body">
		<p class="small text-muted">Alerts reads email with IMAP and sends it with SMTP, using this account. Your email provider lists these settings.</p>

		<x-form.ajax :action="$saveUrl" method="PATCH" id="mail-settings">
			<div class="row">
				<div class="col-md-6">
					<x-form.input name="mail_email" label="Email Address" type="email" :value="$account?->email" required autocomplete="off" />
				</div>
				<div class="col-md-6">
					<x-form.input name="mail_username" label="Username" :value="$account?->username" autocomplete="off" help="Leave empty to use the email address." />
				</div>
			</div>

			<x-form.input name="mail_password" label="Password" type="password" autocomplete="new-password" :required="! $account"
				:help="$account ? 'Leave empty to keep the saved password. It is stored encrypted.' : 'Stored encrypted.'" />

			<div class="row">
				<div class="col-md-6">
					<x-form.input name="imap_host" label="IMAP Server (reading)" :value="$account?->imap_host" required placeholder="imap.example.com" />
				</div>
				<div class="col-6 col-md-3">
					<x-form.input name="imap_port" label="Port" type="number" :value="$account?->imap_port ?? 993" required min="1" max="65535" />
				</div>
				<div class="col-6 col-md-3">
					<x-form.select name="imap_encryption" label="Security" :options="App\Models\MailAccount::ENCRYPTIONS" :value="$account?->imap_encryption ?? 'ssl'" />
				</div>
			</div>

			<div class="row">
				<div class="col-md-6">
					<x-form.input name="smtp_host" label="SMTP Server (sending)" :value="$account?->smtp_host" required placeholder="smtp.example.com" />
				</div>
				<div class="col-6 col-md-3">
					<x-form.input name="smtp_port" label="Port" type="number" :value="$account?->smtp_port ?? 465" required min="1" max="65535" />
				</div>
				<div class="col-6 col-md-3">
					<x-form.select name="smtp_encryption" label="Security" :options="App\Models\MailAccount::ENCRYPTIONS" :value="$account?->smtp_encryption ?? 'ssl'" />
				</div>
			</div>

			<div class="d-flex flex-wrap gap-2">
				<button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Save Mail Settings</button>
				<button type="submit" class="btn btn-outline-secondary" formaction="{{ $testUrl }}"><i class="fas fa-plug me-1"></i> Test Connection</button>
			</div>
		</x-form.ajax>
	</div>

	@if ( $account )
		<div class="card-footer">
			<x-form.ajax :action="$deleteUrl" method="DELETE" class="d-inline" data-confirm="Remove these mail settings? Alerts will stop working until new ones are saved.">
				<button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash me-1"></i> Remove Mail Settings</button>
			</x-form.ajax>
		</div>
	@endif
</div>
