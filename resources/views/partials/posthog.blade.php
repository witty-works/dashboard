posthog.init(
    {!! json_encode(config('posthog.api_key')) !!}, {
    "api_host": {!! json_encode(config('posthog.host'), JSON_UNESCAPED_SLASHES) !!},
    "opt_in_site_apps": true,
    @if (!empty(Auth::user()) || \App\Helpers\PosthogHelper::$posthog_reset)
    "loaded": function(posthog) {
        @if (\App\Helpers\PosthogHelper::$posthog_reset)
        posthog.reset();
        @endif
        @if (!empty(Auth::user()))
        posthog.identify(
            {!! json_encode(Auth::user()->posthogId()) !!}
        );
        @endif
    }
    @endif
});

document.querySelectorAll("[data-attr='posthog-feedback-button']").forEach(el => {
    el.style.display = 'flex';
});

window.addEventListener('PHFeedbackBoxOpened', function (e) {
    window.HelpHero?.setOptions({ show: false })
    window.HelpHero?.setOptions({ showBeacon: false })
});
    
window.addEventListener('PHFeedbackBoxClosed', function (e) {
    window.HelpHero?.setOptions({ show: true })
    window.HelpHero?.setOptions({ showBeacon: true })
    window.HubSpotConversations?.widget?.load();
});
