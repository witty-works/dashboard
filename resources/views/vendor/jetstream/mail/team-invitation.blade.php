@component('mail::message')
{{ __('content.you_have_been_invited', ['team' => $invitation->team->name]) }}

{{ __('content.invitation_email_welcome') }}

@component('mail::button', ['url' => route('dashboard')])
{{ __('content.register') }}
@endcomponent

{{ __('content.if_you_did_not_expect') }}
@endcomponent
