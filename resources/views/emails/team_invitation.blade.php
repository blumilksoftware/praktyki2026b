<x-mail::message>
# {{ __('emails.team_invitation.title') }}

{{ __('emails.team_invitation.body', ['organization' => $organizationName]) }}

{{ __('emails.team_invitation.expiration_message', ['count' => config('auth.invitation.expire', 10080) / 60 / 24]) }}

<x-mail::button :url="url('/invitations/' . $token)">
{{ __('emails.team_invitation.button') }}
</x-mail::button>

{{ __('emails.team_invitation.ignore_notice') }}

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
