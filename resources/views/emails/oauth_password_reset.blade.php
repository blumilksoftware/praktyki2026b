<x-mail::message>
# {{ __('emails.password_reset.oauth_title') }}

{{ __('emails.password_reset.oauth_greeting', ['name' => $user->first_name ?? $user->email]) }}

{{ __('emails.password_reset.oauth_body') }}

<x-mail::button :url="route('login')">
{{ __('emails.password_reset.oauth_cta') }}
</x-mail::button>

{{ __('emails.password_reset.oauth_ignore') }}

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
