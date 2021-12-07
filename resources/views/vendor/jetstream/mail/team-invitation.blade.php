@component('mail::message')
{{ __('content.you_have_been_invited', ['team' => $invitation->team->name]) }}

@if (Laravel\Fortify\Features::enabled(Laravel\Fortify\Features::registration()))
{{ __('content.if_you_do_not_have_an_account') }}

@component('mail::button', ['url' => route('register')])
{{ __('content.create_account') }}
@endcomponent

{{ __('content.if_you_already_have_an_account') }}

@else
{{ __('content.you_may_accept_this_invitiation') }}
@endif

@component('mail::button', ['url' => route('login')])
{{ __('content.log_in') }}
@endcomponent

{{ __('content.if_you_did_not_expect') }}
@endcomponent
