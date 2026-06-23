<x-mail::message>
# {{ __('emails.job_application.new_title') }}

{{ __('emails.job_application.new_body', ['student_name' => $studentName, 'job_title' => $jobTitle]) }}

<x-mail::button :url="$applicationUrl">
{{ __('emails.job_application.new_cta') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
