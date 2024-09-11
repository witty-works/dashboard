<script nonce="{{ csp_nonce('script') }}">
    window.default_locale = {!! json_encode(config('app.locale')) !!};
    window.fallback_locale = {!! json_encode(config('app.fallback_locale')) !!};
    window.sentry_dsn = {!! json_encode(config('sentry.dsn')) !!};
    window.sentry_sample_rate = {!! json_encode(config('sentry.traces_sample_rate')) !!};
    window.app_name = {!! json_encode(config('app.name')) !!};
    window.sentry_release = {!! json_encode(config('sentry.release')) !!};
</script>