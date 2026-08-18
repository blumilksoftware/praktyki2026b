<x-mail::message>
# {{ __('emails.partnership.requested_title') }}

{{ __('emails.partnership.requested_body', ['proposer_name' => $proposerName]) }}

<x-mail::button :url="$dashboardUrl">
{{ __('emails.partnership.requested_cta') }}
</x-mail::button>

{{ __('emails.verification.all_rights_reserved') }}
</x-mail::message>
