@php
    $browserExtensionInstalled = true;
    if(isset($_GET['install-witty'])) {
        $browserExtensionInstalled = false;
    }
@endphp


<script>
    window.addEventListener('load', () => {
        const wittyIsInstalled = document.querySelector('witty-is-installed') || document.querySelector('witty-code');
        if (wittyIsInstalled && window.location.href.includes("?install-witty")) {
            window.history.replaceState({}, '', window.location.href.replace('?install-witty', ''));
            window.location.reload();
        } 
        else if (!wittyCode && !window.location.href.includes("?install-witty")) {
            window.location.href = "?install-witty";
        }
    });
</script>

@if(!$browserExtensionInstalled)
    <div class="wittyworks-upgrade-banner">
        <div class="wittyworks-upgrade-banner-text-container">
            <div class="wittyworks-upgrade-banner-title">
             {{ __('content.onboarding_install_witty_title') }}
            </div>
            <div class="wittyworks-upgrade-banner-text">
            {{ __('content.onboarding_install_witty_text') }}
            </div>
        </div>
        <div class="wittyworks-upgrade-banner-button-container">
            <a class="wittyworks-upgrade-banner-button" href="https://www.witty.works/select-browser" target="_blank" rel="noopener">
                {{ __('content.onboarding_install_witty_button') }}
            </a>
        </div>
    </div>
@endif