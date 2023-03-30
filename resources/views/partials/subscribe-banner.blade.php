<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner">
    <div>
        <div class="wittyworks-upgrade-banner-title">
            {!! __('content.upgrade_title') !!}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            @if($user->ownsTeam($user->currentTeam))
            {!! __('content.upgrade_text') !!}
            @else
            {!! __('content.upgrade_ask_owner_text', ['name' => $team->owner->name, 'email' => $team->owner->email]) !!}
            @endif
        </div>
    </div>

    <div class="wittyworks-upgrade-banner-button-container">
        @if($user->ownsTeam($user->currentTeam))
        <a class="button primary-button-purple" href="{{ route('teams.subscription') }}">
            {{ __('content.upgrade_button') }}
        </a>
        @endif
    </div>
</div>
