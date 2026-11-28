@component('mail::message')
{!! __('content.you_have_been_invited', ['name' => e($name), 'team' => e($team), 'email' => e($email)]) !!}

{!! __('content.invitation_email_welcome') !!}

@if ($asOwner)
{{ __('content.invitation_as_owner', ['team' => $team]) }}
@endif

@component('mail::button', ['url' => $acceptUrl])
{{ __('content.accept_invitation') }}
@endcomponent

{!! __('content.if_you_did_not_expect') !!}

{!! __('content.have_a_great_day') !!}

{!! __('content.if_you_have_questions', ['support_email' => e(config('app.support_email'))]) !!}
@endcomponent
