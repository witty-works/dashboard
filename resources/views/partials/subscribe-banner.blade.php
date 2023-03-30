<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner">
    <div>
        <div class="wittyworks-upgrade-banner-title">
            {!! __('content.upgrade_title') !!}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            @if($user->ownsTeam($user->currentTeam))
            {!! __('content.upgrade_text') !!}
            @else
            {!! __('content.upgrade_ask_owner_text') !!}
            @endif
        </div>
    </div>

    <div class="wittyworks-upgrade-banner-button-container">
        @if($user->ownsTeam($user->currentTeam))
        <a class="button primary-button-purple" href="{{ route('teams.subscription') }}">
            {{ __('content.upgrade_button') }}
        </a>
        @else
        {{ $team->owner->name }} (<a href="mailto:{{ $team->owner->email }}">{{ $team->owner->email }}</a>)
    @endif
    </div>
</div>
