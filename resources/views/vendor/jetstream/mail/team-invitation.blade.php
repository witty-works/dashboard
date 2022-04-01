@component('mail::message')
{{ __('content.you_have_been_invited', ['team' => $invitation->team->name]) }}

{{ __('content.invitation_email_welcome') }}

@component('mail::button', ['url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'register'])])
{{ __('content.register') }}
@endcomponent

{{ __('content.invitation_email_login') }}

@component('mail::button', ['url' => route('oauth.redirect', ['provider' => 'azureadb2c', 'policy' => 'login'])])
{{ __('content.log_in') }}
@endcomponent

{{ __('content.if_you_did_not_expect') }}
@endcomponent
