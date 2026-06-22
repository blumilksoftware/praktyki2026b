<x-mail::message>
# {{ __('emails.verification.accept_mail_title') }}

{{ __('emails.verification.accept_mail_greeting', ['name' => $university->name]) }}

{{ __('emails.verification.accept_mail_body') }}


<x-mail::button :url="route('university.dashboard')">
{{ __('emails.verification.accept_mail_cta_text') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
