<script>
    window.addEventListener('load', () => {
        const wittyIsInstalled = document.querySelector('witty-is-installed');

        const installWitty = document.querySelector('#install-witty');
        const loginWitty = document.querySelector('#login-witty');

        if (!wittyIsInstalled) {
            installWitty.style.display = 'flex';
            installWitty.classList.remove('opacity-0');
            installWitty.classList.add('opacity-100');
            loginWitty.style.display = 'none';
        } else {
            installWitty.style.display = 'none';

            loginUrl = wittyIsInstalled.getAttribute('login-url')
            if (loginUrl) {
                const loginWittyUrl = document.getElementById('login-witty-url');
                loginWittyUrl.setAttribute('href', loginUrl + '?target=' + encodeURIComponent(window.location.href))

                loginWitty.style.display = 'flex';
                loginWitty.classList.remove('opacity-0');
                loginWitty.classList.add('opacity-100');
            } else {
                loginWitty.style.display = 'none';
            }
        }
    });
</script>

<div id="install-witty" class="wittyworks-upgrade-banner btn-fade-in bg-teal-500 opacity-0 hover:bg-teal-600 text-white font-bold py-2 px-4 rounded mr-2 transition-all duration-1000 ease-in-out">
    <div>
        <div class="wittyworks-upgrade-banner-title">
            {{ __('content.onboarding_install_witty_title') }}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            {{ __('content.onboarding_install_witty_text') }}
        </div>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a class="wittyworks-button wittyworks-button--purple" href="https://www.witty.works/select-browser" target="_blank" rel="noopener">
            {{ __('content.onboarding_install_witty_button') }}
        </a>
    </div>
</div>

<div id="login-witty" class="wittyworks-upgrade-banner btn-fade-in bg-teal-500 opacity-0 hover:bg-teal-600 text-white font-bold py-2 px-4 rounded mr-2 transition-all duration-1000 ease-in-out">
    <div>
        <div class="wittyworks-upgrade-banner-title">
            {{ __('content.onboarding_login_witty_title') }}
        </div>
        <div class="wittyworks-upgrade-banner-text">
            {{ __('content.onboarding_login_witty_text') }}
        </div>
    </div>
    <div class="wittyworks-upgrade-banner-button-container">
        <a id="login-witty-url" class="wittyworks-button wittyworks-button--purple" href="">
            {{ __('content.onboarding_login_witty_button') }}
        </a>
    </div>
</div>