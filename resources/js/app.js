import './bootstrap';

import * as Sentry from "@sentry/browser";

if (window.sentry_dsn) {
    Sentry.init({
        dsn: window.sentry_dsn,
        release: window.app_name + "@" + window.sentry_release,
        tracesSampleRate: window.traces_sample_rate,
        tracingOptions: {
            trackComponents: true,
        }
    });
}
