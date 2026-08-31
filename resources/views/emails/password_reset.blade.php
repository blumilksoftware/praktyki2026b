<x-mail::message>
# {{ __('emails.password_reset.title') }}

{{ __('emails.password_reset.greeting', ['name' => $user->first_name ?? $user->email]) }}

{{ __('emails.password_reset.body') }}

<x-mail::button :url="$url">
{{ __('emails.password_reset.cta') }}
</x-mail::button>

{{ __('emails.password_reset.expiration_message', ['count' => $expiresInMinutes]) }}

{{ __('emails.password_reset.ignore') }}

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
