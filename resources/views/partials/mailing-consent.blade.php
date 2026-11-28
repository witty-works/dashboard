<div class="wittyworks-upgrade-banner" id="wittyworks-upgrade-banner-mailing">
    <div>
        <h2 class="wittyworks-upgrade-banner-title">
            {!! __('content.mailing_consent_title') !!}
        </h2>
        <p class="wittyworks-upgrade-banner-text">
            {!! __('content.mailing_consent_text') !!}
        </p>
    </div>

    <div class="wittyworks-upgrade-banner-button-container">

          <a class="button primary-button-purple" href="{{ route('user.mailing_consent') }}?consent=1">
              {{ __('content.mailing_consent_button') }}
          </a>

        <a class="button secondary-button-purple" href="{{ route('user.mailing_consent') }}?consent=0">
            {{ __('content.mailing_consent_reject') }}
        </a>

    </div>
</div>
