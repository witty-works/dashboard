@component('mail::message')
{!! __('content.invitation_request_accepted_explanation', ['team' => $team, 'name' => $name, 'email' => $email]) !!}

{!! __('content.have_a_great_day') !!}

{!! __('content.if_you_have_questions') !!}
@endcomponent
