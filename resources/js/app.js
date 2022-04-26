require('./bootstrap');

import * as Sentry from "@sentry/browser";
import { Integrations } from "@sentry/tracing";

window.addEventListener('load', () => {
    const wittyCode = document.querySelector('witty-code');
    if (wittyCode) {
        const extensionId = wittyCode.getAttribute('extension-id');
        const usersOptionPage = `chrome-extension://${extensionId}/options.html`;
        console.log('witty is installed', usersOptionPage);
    } 
    else {
        console.log('Witty not installed');
    }
});

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

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
