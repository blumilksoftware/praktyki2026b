<x-mail::message>
# {{ __('emails.job_application.status_changed_title') }}

{{ __('emails.job_application.status_changed_body', ['company_name' => $companyName, 'job_title' => $jobTitle, 'status' => $status]) }}

<x-mail::button :url="$dashboardUrl">
{{ __('emails.job_application.status_changed_cta') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
