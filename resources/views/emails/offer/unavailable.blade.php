<x-mail::message>
# {{ __('emails.offer.unavailable_title') }}

{{ __('emails.offer.unavailable_body', ['job_title' => $jobTitle, 'company_name' => $companyName, 'reason' => $reasonText]) }}
</x-mail::message>
