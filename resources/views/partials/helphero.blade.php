@if (config('helphero.js_enabled'))
@php
  $user = Auth::user();
  $userParameters = [
    'role' => $user->teamRole($user->currentTeam),
    'plan' => $user->planId(),
    'created_at' => $user->created_at,
    'team_id' => $user->posthogTeamId(),
];
@endphp
<script src="//app.helphero.co/embed/{{ config('helphero.app_id') }}"></script>
<script>
    if (window.HelpHero) {
        @if(empty($user))
        HelpHero.anonymous();
        @else
        HelpHero.identify({!! json_encode($user->posthogId()) !!}, {!! json_encode($userParameters) !!});
        @endif
    }
</script>
@endif