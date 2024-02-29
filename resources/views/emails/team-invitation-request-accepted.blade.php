@component('mail::message')
@if($role === 'user')
{!! __('content.invitation_request_accepted_explanation_user', ['team' => $team, 'name' => $name, 'email' => $email]) !!}
@else
{!! __('content.invitation_request_accepted_explanation_admin', ['team' => $team, 'name' => $name, 'email' => $email]) !!}
@endif
{!! __('content.have_a_great_day') !!}

{!! __('content.if_you_have_questions') !!}
@endcomponent
