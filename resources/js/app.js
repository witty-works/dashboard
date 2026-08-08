import './bootstrap';

import * as Sentry from "@sentry/browser";

// Accessible info tooltips (partials/info_hover.blade.php): shown on hover,
// focus, or click; kept open while the pointer is over the panel; dismissed
// with Escape (WCAG 1.4.13).
let infoHoverHideTimer = null;

function infoHoverHideAll() {
    document.querySelectorAll('.information-image').forEach((panel) => {
        panel.style.zIndex = '-1';
        panel.style.visibility = 'hidden';
    });
    document.querySelectorAll('.info-hover-trigger[aria-expanded="true"]').forEach((trigger) => {
        trigger.setAttribute('aria-expanded', 'false');
    });
}

function infoHoverShow(trigger) {
    infoHoverCancelHide();
    infoHoverHideAll();
    const panel = document.getElementById(trigger.getAttribute('aria-controls'));
    if (!panel) {
        return;
    }
    const rect = trigger.getBoundingClientRect();
    panel.style.left = Math.max(0, rect.left + window.scrollX - 200) + 'px';
    panel.style.top = (rect.bottom + window.scrollY + 10) + 'px';
    panel.style.zIndex = '1';
    panel.style.visibility = 'visible';
    trigger.setAttribute('aria-expanded', 'true');
}

function infoHoverScheduleHide() {
    infoHoverCancelHide();
    infoHoverHideTimer = setTimeout(infoHoverHideAll, 300);
}

function infoHoverCancelHide() {
    if (infoHoverHideTimer) {
        clearTimeout(infoHoverHideTimer);
        infoHoverHideTimer = null;
    }
}

window.infoHoverShow = infoHoverShow;
window.infoHoverScheduleHide = infoHoverScheduleHide;
window.infoHoverCancelHide = infoHoverCancelHide;
window.infoHoverHideAll = infoHoverHideAll;

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        infoHoverHideAll();
    }
});

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
