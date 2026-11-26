@php
    // Set after an account deletion, for the first page that loads PostHog.
    $posthogReset = \App\Helpers\PosthogHelper::$posthog_reset || session()->pull('posthog_reset', false);
@endphp
posthog.init(
    {!! json_encode(config('posthog.api_key')) !!}, {
    "api_host": {!! json_encode(config('posthog.host'), JSON_UNESCAPED_SLASHES) !!},
    "opt_in_site_apps": true,
    @if ($posthogReset)
    // The page view is captured after the reset, not under the old ID.
    "capture_pageview": false,
    @endif
    @if (!empty(Auth::user()) || $posthogReset)
    "loaded": function(posthog) {
        @if ($posthogReset)
        posthog.reset();
        posthog.capture('$pageview');
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
});

window.addEventListener('PHSurveyClosed', function (e) {
    window.PHSurveyClosed = true;
    window.HelpHero?.setOptions({ showBeacon: true })
});
