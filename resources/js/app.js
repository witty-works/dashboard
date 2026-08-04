import './bootstrap';

import * as Sentry from "@sentry/browser";

if (window.sentry_dsn) {
    // Performance tracing stays off unless SENTRY_TRACES_SAMPLE_RATE is set and
    // Sentry.browserTracingIntegration() is added to `integrations`; a sample
    // rate on its own does nothing in the v8+ browser SDK.
    Sentry.init({
        dsn: window.sentry_dsn,
        release: window.app_name + "@" + window.sentry_release,
        tracesSampleRate: window.sentry_sample_rate,
    });
}
