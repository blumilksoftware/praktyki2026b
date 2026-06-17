<x-mail::message>
# {{ __("emails.registration.greeting", ["name" => $user->first_name]) }}

{{ __("emails.registration.body") }}

## {{ __("emails.registration.details_heading") }}

| | |
|---|---|
| **{{ __("emails.registration.field_name") }}** | {{ $user->first_name }} {{ $user->last_name }} |
| **{{ __("emails.registration.field_email") }}** | {{ $user->email }} |
@if ($user->university)
| **{{ __("emails.registration.field_university") }}** | {{ $user->university }} |
@endif

{{ __("emails.registration.ignore_notice") }}

{{ __("emails.registration.all_rights_reserved") }}<br>
{{ config("app.name") }}
</x-mail::message>