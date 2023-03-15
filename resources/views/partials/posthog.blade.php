@if (config('posthog.js_enabled'))
<script>
    if (window.posthog) {
        window.posthog.init(
            {!! json_encode(config('posthog.api_key')) !!}, {
            api_host: {!! json_encode(config('posthog.host'), JSON_UNESCAPED_SLASHES) !!},
            opt_in_site_apps: true,
            @if (!empty(Auth::user()) || \App\Helpers\PosthogHelper::$posthog_reset)
            loaded: function(posthog) {
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
    }
</script>
@endif