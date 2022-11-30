<script>
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
            const newestVersion = @json(config('app.browser_version'));
            if (extensionVersion !== newestVersion) { 
                upgradeWittyVersion.style.display = 'flex';
            }
        }
    });
</script>

    <div id="install-witty" style="display: none" class="wittyworks-upgrade-banner">
        <div>
            <div class="wittyworks-upgrade-banner-title">
                {{ __('content.onboarding_install_witty_title') }}
            </div>
            <div class="wittyworks-upgrade-banner-text">
                {{ __('content.onboarding_install_witty_text') }}
            </div>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="button primary-button-purple" href="https://www.witty.works/download" target="_blank" rel="noopener">
                {{ __('content.onboarding_install_witty_button') }}
            </a>
        </div>
    </div>

    <div id="login-witty" style="display: none" class="wittyworks-upgrade-banner">
        <div>
            <div class="wittyworks-upgrade-banner-title">
                {{ __('content.onboarding_login_witty_title') }}
            </div>
            <div class="wittyworks-upgrade-banner-text">
                {{ __('content.onboarding_login_witty_text') }}
            </div>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a id="login-witty-url" class="button primary-button-purple" href="">
                {{ __('content.onboarding_login_witty_button') }}
            </a>
        </div>
    </div>

    <div id="upgrade-witty-version" style="display: none" class="wittyworks-upgrade-banner">
        <div>
            <div class="wittyworks-upgrade-banner-title">
                {{ __('content.upgrade_witty_version_title') }}
            </div>
            <div class="wittyworks-upgrade-banner-text">
                {{ __('content.upgrade_witty_version_text') }}
            </div>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="button primary-button-purple" href="https://www.witty.works/select-browser" target="_blank" rel="noopener">
                {{ __('content.upgrade_witty_version_button') }}
            </a>
        </div>
    </div>
