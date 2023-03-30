<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner">
    <div>
        <div class="wittyworks-upgrade-banner-title">
            {!! __('content.invition_requests_title') !!}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            {!! __('content.invition_requests_text') !!}
        </div>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a class="button primary-button-purple" href="{{ route('teams.show').'#requests' }}">
            {{ __('content.review_invitation_requests_button') }}
        </a>
    </div>
</div>
