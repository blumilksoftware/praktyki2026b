<x-mail::message>
# {{ __('emails.email_change.title') }}

{{ __('emails.email_change.status_message', ['email' => $newEmail]) }}

{{ __('emails.email_change.expiration_message', ['count' => config('auth.verification.expire', 1440) / 60]) }}

{{ __('emails.email_change.action_text') }}

<x-mail::button :url="url('/email/change/confirm/' . $user->id . '/' . $token)">
{{ __('emails.email_change.button') }}
</x-mail::button>

{{ __('emails.email_change.ignore_notice') }}

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>