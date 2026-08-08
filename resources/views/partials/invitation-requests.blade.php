<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner-requests">
    <div>
        <h2 class="wittyworks-upgrade-banner-title">
            {!! __('content.invition_requests_title') !!}
        </h2>
        <p class="wittyworks-upgrade-banner-text">
            {!! __('content.invition_requests_text') !!}
        </p>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a class="button primary-button-purple" href="{{ route('teams.show').'#requests' }}">
            {{ __('content.review_invitation_requests_button') }}
        </a>
    </div>
</div>
