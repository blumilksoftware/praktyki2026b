<x-mail::message>
# {{ __('emails.job_application.reminder.greeting', ['company_name' => $companyName]) }}

{{ __('emails.job_application.reminder.line_1', ['job_title' => $jobTitle, 'days' => $daysPending]) }}

{{ __('emails.job_application.reminder.line_2') }}

{{ $applicationUrl }}

<x-mail::button :url="$applicationUrl">
{{ __('emails.job_application.reminder.action') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
