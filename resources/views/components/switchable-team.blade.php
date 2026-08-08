@props(['team', 'component' => 'dropdown-link'])

<form method="POST" action="{{ route('current-team.update') }}">
    @method('PUT')
    @csrf

    <!-- Hidden Team ID -->
    <input type="hidden" name="team_id" value="{{ $team->id }}">

    <button type="submit"
        class="navigation-link wittyworks-navigation-sub-sub-link lato-small-text-p w-full text-left {{ Auth::user()->isCurrentTeam($team) ? 'navigation-link-active' : '' }}"
        @if (Auth::user()->isCurrentTeam($team)) aria-current="true" @endif>
        <div class="truncate">{{ $team->name }}</div>
    </button>
</form>
