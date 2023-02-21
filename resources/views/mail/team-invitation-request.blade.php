@component('mail::message')
{!! __('content.invitation_request_explanation', ['name' => $name, 'team' => $team, 'email' => $email], $language) !!}

@component('mail::button', ['url' => route('teams.show').'#requests'])
{{ __('content.accept_invitation_request', ['name' => $name, 'team' => $team, 'email' => $email], $language) }}
@endcomponent

{!! __('content.if_you_did_not_expect_request', ['team' => $team, 'name' => $name, 'email' => $email], $language) !!}

{!! __('content.have_a_great_day', [], $language) !!}

{!! __('content.if_you_have_questions', [], $language) !!}
@endcomponent
