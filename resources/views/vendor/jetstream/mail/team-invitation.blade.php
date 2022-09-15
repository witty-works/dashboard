@component('mail::message')
{!! __('content.you_have_been_invited', ['name' => $name, 'team' => $team, 'email' => $email]) !!}

{!! __('content.invitation_email_welcome') !!}

{!! __('content.if_you_did_not_expect') !!}

{!! __('content.have_a_great_day') !!}

{!! __('content.if_you_have_questions') !!}
@endcomponent
