<script nonce="{{ csp_nonce('script') }}">
    window.addEventListener('load', () => {
        const wittyIsInstalled = document.querySelector('witty-is-installed');

        const installWitty = document.querySelector('#install-witty');
        const loginWitty = document.querySelector('#login-witty');
        const upgradeWittyVersion = document.querySelector('#upgrade-witty-version');

        if (!wittyIsInstalled) {
            installWitty.style.display = 'flex';
        } else {
            loginUrl = wittyIsInstalled.getAttribute('login-url')
            if (loginUrl) {
                const loginWittyUrl = document.getElementById('login-witty-url');
                loginWittyUrl.setAttribute('href', loginUrl + '?target=' + encodeURIComponent(window.location.href))
                loginWitty.style.display = 'flex';
            }

            const extensionVersion = wittyIsInstalled.getAttribute('extension-version');
            const browsers = @json(config('app.browsers'));
            const browser = detectBrowser()

            if (browser) {
                const newestVersions = browsers[browser]['latest_version'];

                if (extensionVersion
                    && newestVersions
                    && -1 === new Intl.Collator('en').compare(extensionVersion, newestVersions)
                ) {
                    let url = 'https://www.witty.works/en/help/how-can-i-update-witty';

                    const wittyOptionsUrl = document.getElementById('witty-version-options-url');
                    wittyOptionsUrl.setAttribute('href', url)

                    upgradeWittyVersion.style.display = 'flex';
                }
            }
        }
    });
</script>

    <div id="install-witty" style="display: none" class="wittyworks-upgrade-banner" role="alert" aria-labelledby="install-witty-title" aria-describedby="install-witty-text">
        <div>
            <h2 id="install-witty-title" class="wittyworks-upgrade-banner-title">
                {!! __('content.onboarding_install_witty_title') !!}
            </h2>
            <p id="install-witty-text" class="wittyworks-upgrade-banner-text">
                {!! __('content.onboarding_install_witty_text') !!}
            </p>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a href="{{ route('download') }}" class="button primary-button-purple" role="button">
                {{ __('content.onboarding_install_witty_button') }}
            </a>
        </div>
    </div>

    <div id="login-witty" style="display: none" class="wittyworks-upgrade-banner" role="alert" aria-labelledby="login-witty-title" aria-describedby="login-witty-text">
        <div>
            <h2 id="login-witty-title" class="wittyworks-upgrade-banner-title">
                {!! __('content.onboarding_login_witty_title') !!}
            </h2>
            <p id="login-witty-text" class="wittyworks-upgrade-banner-text">
                {!! __('content.onboarding_login_witty_text') !!}
            </p>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a id="login-witty-url" href="#" class="button primary-button-purple" role="button">
                {{ __('content.onboarding_login_witty_button') }}
            </a>
        </div>
    </div>

    <div id="upgrade-witty-version" style="display: none" class="wittyworks-upgrade-banner" role="alert" aria-labelledby="upgrade-witty-title" aria-describedby="upgrade-witty-text">
        <div>
            <h2 id="upgrade-witty-title" class="wittyworks-upgrade-banner-title">
                {!! __('content.update_witty_version_title') !!}
            </h2>
            <p id="upgrade-witty-text" class="wittyworks-upgrade-banner-text">
                {!! __('content.update_witty_version_text') !!}
            </p>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a id="witty-version-options-url" class="button primary-button-purple" target="_blank" rel="noopener noreferrer" role="button" aria-label="{{ __('content.update_witty_version_button') . ' (opens in a new window)' }}">
                {{ __('content.update_witty_version_button') }}
            </a>
        </div>
    </div>