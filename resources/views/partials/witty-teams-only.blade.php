@php
    $url = $url ?? route('teams.subscription');
@endphp
<a class="witty-teams-only"
    href="{{ $url }}"
    target="_blank"
    rel="noopener"
    title="{{ __('teams.upgrade_to_witty_teams') }}"
>
    {{ __('teams.witty_teams_only') }}
</a>
