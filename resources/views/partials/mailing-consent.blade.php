<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner">
    <div class="wittyworks-upgrade-banner-text-container">
        <div class="wittyworks-upgrade-banner-title">
            {{ __('content.mailing_consent_title') }}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            {{ __('content.mailing_consent_text') }}
        </div>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a class="wittyworks-button wittyworks-button--purple" href="{{ route('user.mailing_consent') }}">
            {{ __('content.mailing_consent_button') }}
        </a>
    </div>
</div>
