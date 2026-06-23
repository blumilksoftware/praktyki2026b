<x-mail::message>
# {{ __('emails.verification.reject_mail_title') }}

{{ __('emails.verification.reject_mail_greeting', ['name' => $company->name]) }}

{{ __('emails.verification.reject_mail_body') }}

@if($rejectionReason)
## {{ __('emails.verification.reject_mail_reason_heading') }}

{{ $rejectionReason }}
@endif

{{ __('emails.verification.reject_mail_next_steps') }}

<x-mail::button :url="route('login')">
{{ __('emails.verification.reject_mail_support_text') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
