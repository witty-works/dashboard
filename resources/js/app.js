require('./bootstrap');

import * as Sentry from "@sentry/browser";
import { Integrations } from "@sentry/tracing";

if (window.sentry_dsn) {
    Sentry.init({
        dsn: window.sentry_dsn,
        release: window.app_name + "@" + window.sentry_release,
        integrations: [new Integrations.BrowserTracing()],
        tracesSampleRate: window.traces_sample_rate,
        tracingOptions: {
            trackComponents: true,
        }
    });
}