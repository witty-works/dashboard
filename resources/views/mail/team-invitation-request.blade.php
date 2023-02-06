@component('mail::message')
{!! __('content.invition_request_send', ['name' => $name, 'team' => $team, 'email' => $email]) !!}

{!! __('content.invitation_request_explanation') !!}

@component('mail::button', ['url' => route('teams.show').'#requests'])
{{ __('content.accept_invitation_request') }}
@endcomponent

{!! __('content.if_you_did_not_expect_request') !!}

{!! __('content.have_a_great_day') !!}

{!! __('content.if_you_have_questions') !!}
@endcomponent
