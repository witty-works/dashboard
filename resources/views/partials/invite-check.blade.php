<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner-invite">
    <div>
        <h2 class="wittyworks-upgrade-banner-title">
            {!! __('content.invite_team_members_title') !!}
        </h2>
        <p class="wittyworks-upgrade-banner-text">
            {!! __('content.invite_team_members_text') !!}
        </p>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a class="button primary-button-purple" href="{{ route('teams.show') }}">
            {{ __('content.invite_team_members_button') }}
        </a>
    </div>
</div>
