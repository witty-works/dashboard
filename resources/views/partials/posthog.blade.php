@if (config('posthog.js_enabled'))
<script>
    if (window.posthog) {
        window.posthog.init(
            {!! json_encode(config('posthog.api_key')) !!}, {
            api_host: {!! json_encode(config('posthog.host'), JSON_UNESCAPED_SLASHES) !!},
            loaded: function(posthog) {
                @if (\App\Http\Middleware\PostHogMiddleware::$reset)
                posthog.reset();
                @endif
                @if (!empty(Auth::user()))
                posthog.identify(
                    {!! json_encode(Auth::user()->posthogId()) !!}
                );
                @endif
            }
        });
    }
</script>
@endif