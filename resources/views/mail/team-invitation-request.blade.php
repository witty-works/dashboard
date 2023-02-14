@component('mail::message')
{!! __('content.invitation_request_explanation', ['name' => $name, 'team' => $team, 'email' => $email]) !!}

@component('mail::button', ['url' => route('teams.show').'#requests'])
{{ __('content.accept_invitation_request', ['name' => $name, 'team' => $team, 'email' => $email]) }}
@endcomponent

{!! __('content.if_you_did_not_expect_request', ['team' => $team, 'name' => $name, 'email' => $email]) !!}

{!! __('content.have_a_great_day') !!}

{!! __('content.if_you_have_questions') !!}
@endcomponent
