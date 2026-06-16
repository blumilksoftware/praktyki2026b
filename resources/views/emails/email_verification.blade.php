<x-mail::message>
# {{ __('emails.verification.title') }}

{{ __('emails.verification.status_message', ['email' => $user->email]) }}

{{ __('emails.verification.expiration_message', ['count' => config('auth.verification.expire', 1440) / 60]) }}

{{ __('emails.verification.action_text') }}

<x-mail::button :url="url('/email/verify/' . $user->id . '/' . $token)">
{{ __('emails.verification.button') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
