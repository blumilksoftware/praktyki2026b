<x-mail::message>
# {{ __('emails.verification.accept_mail_title') }}

{{ __('emails.verification.accept_mail_greeting', ['name' => $company->name]) }}

{{ __('emails.verification.accept_mail_body') }}

<x-mail::button :url="route('company.profile')">
{{ __('emails.verification.accept_mail_cta_text') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
