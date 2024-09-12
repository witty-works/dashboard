<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-PJGMPB9"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->

<script nonce="{{ csp_nonce('script') }}">
window.dataLayer = window.dataLayer || [];
window._hsp = window._hsp || [];
_hsp.push(['addPrivacyConsentListener', function(consent) {
    if (consent.categories.analytics) {
        dataLayer.push({'event': 'cookie_consent_update'});

@if (config('posthog.js_enabled'))
    @include('partials/posthog')
@endif
    }
}]);
</script>