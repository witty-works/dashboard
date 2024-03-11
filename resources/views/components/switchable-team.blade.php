@props(['team', 'component' => 'dropdown-link'])

<form method="POST" action="{{ route('current-team.update') }}" x-data>
    @method('PUT')
    @csrf

    <!-- Hidden Team ID -->
    <input type="hidden" name="team_id" value="{{ $team->id }}">

    <x-nav-link class="navigation-link wittyworks-navigation-sub-link lato-small-text-p" x-on:click.prevent="$root.submit();" href="#" :active="Auth::user()->isCurrentTeam($team)">
        <div class="truncate">{{ $team->name }}</div>
    </x-nav-link>
</form>
