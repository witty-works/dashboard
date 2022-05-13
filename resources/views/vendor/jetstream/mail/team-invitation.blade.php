@component('mail::message')
{!! __('content.you_have_been_invited', ['name' => $name, 'team' => $team, 'email' => $email]) !!}

{!! __('content.invitation_email_welcome') !!}

@component('mail::button', ['url' => route('dashboard')])
{!! __('content.register') !!}
@endcomponent

{!! __('content.if_you_did_not_expect') !!}

{!! __('content.if_you_have_questions') !!}

{!! __('content.have_a_great_day') !!}
@endcomponent
