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

window.addEventListener('PHSurveyShown', function (e) {
    window.PHSurveyClosed = false;
    window.HelpHero?.setOptions({ showBeacon: false })
    if (window.HubSpotConversations?.widget) {
        window.HubSpotConversations?.widget?.remove()
    }
});

window.addEventListener('PHSurveyClosed', function (e) {
    window.PHSurveyClosed = true;
    window.HelpHero?.setOptions({ showBeacon: true })
    if (window.HubSpotConversations?.widget) {
        window.HubSpotConversations?.widget?.load();
    }
});
