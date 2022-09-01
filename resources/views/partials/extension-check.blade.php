@php
    $browserExtensionInstalled = true;
    if(isset($_GET['install-witty'])) {
        $browserExtensionInstalled = false;
    }
@endphp


<script>
    window.addEventListener('load', () => {
        const wittyIsInstalled = document.querySelector('witty-is-installed') || document.querySelector('witty-code');
        const banner = document.querySelector('#wittyworks-upgrade-banner');

        if(!wittyIsInstalled) {
            banner.style.display = 'flex';
            banner.classList.remove('opacity-0');
            banner.classList.add('opacity-100');
        } else {
            banner.style.display = 'none';
            banner.classList.add('opacity-0');

        }
    });
</script>

    <div class="wittyworks-upgrade-banner btn-fade-in bg-teal-500 opacity-0 hover:bg-teal-600 text-white font-bold py-2 px-4 rounded mr-2 transition-all duration-1000 ease-in-out" id="wittyworks-upgrade-banner">
        <div class="wittyworks-upgrade-banner-text-container">
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
