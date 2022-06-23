@php
    $url = $url ?? 'https://www.witty.works/witty-for-teams';
@endphp
<a class="witty-teams-only"
    href="{{ $url }}"
    target="_blank"
    rel="noopener"
    {{ __('teams.upgrade_to_witty_teams') }}" title="{{ __('teams.upgrade_to_witty_teams') }}"
>
    {{ __('teams.witty_teams_only') }}
</a>