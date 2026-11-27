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

// Triple toggles (components/triple-toggle.blade.php): off, basic, advanced.
// minvalue says how far down the team's setting lets the user go.
function tripleToggle(toggle) {
    const name = toggle.id.replace('tripple-toggle-', '');
    const hiddenInput = document.getElementById(name);
    const announcement = document.getElementById('toggle-announcement-' + name);
    const set = (value, pressed, message) => {
        hiddenInput.setAttribute('value', value);
        toggle.setAttribute('aria-pressed', pressed);
        announcement.innerText = toggle.dataset[message];
    };
    const toBasic = () => {
        toggle.classList.add('active', 'middle');
        set('1', 'mixed', 'messageBasic');
    };

    switch (toggle.getAttribute('minvalue')) {
    case '0':
        if (!toggle.classList.contains('active')) {
            toBasic();
        } else if (toggle.classList.contains('middle')) {
            toggle.classList.remove('middle');
            set('2', 'true', 'messageAdvanced');
        } else {
            toggle.classList.remove('active');
            set('0', 'false', 'messageOff');
        }
        break;
    case '1':
        if (toggle.classList.contains('middle')) {
            toggle.classList.remove('middle');
            set('2', 'true', 'messageAdvanced');
        } else {
            toBasic();
        }
        break;
    default:
        return;
    }

    hiddenInput.dispatchEvent(new Event('input'));
}

// The proficiency levels' "more" buttons (*-category-settings/category.blade.php).
function toggleDetails(button) {
    const open = button.getAttribute('aria-expanded') !== 'true';
    const details = document.getElementById(button.getAttribute('aria-controls'));
    if (details) {
        details.style.display = open ? 'block' : 'none';
    }
    button.querySelectorAll('[data-arrow]').forEach((arrow) => {
        arrow.style.display = (arrow.dataset.arrow === 'up') === open ? 'block' : 'none';
    });
    button.setAttribute('aria-expanded', open ? 'true' : 'false');
}

// The edit buttons of the dictionary, ignored words and domains lists
// (livewire/*/show.blade.php) lead to the field the entry is edited in, and
// move focus there too, so that keyboard and screen reader users land on the
// form (WCAG 2.4.3). Livewire fills the field when its answer arrives.
function scrollToField(button) {
    const field = document.getElementById(button.dataset.scrollTo);
    if (!field) {
        return;
    }
    field.scrollIntoView({behavior: 'smooth', block: 'center'});
    field.focus({preventScroll: true});
}

// The views attach no inline handlers (onclick= and the like), which the
// Content-Security-Policy blocks; these listeners stand in for them. They are
// on the document, so they also cover what Livewire renders later.
const closest = (event, selector) => (event.target instanceof Element ? event.target.closest(selector) : null);

document.addEventListener('click', (event) => {
    let element;
    if ((element = closest(event, '.info-hover-trigger'))) {
        infoHoverShow(element);
    } else if ((element = closest(event, '[data-triple-toggle]'))) {
        tripleToggle(element);
    } else if ((element = closest(event, '[data-toggle-details]'))) {
        toggleDetails(element);
    } else if ((element = closest(event, '[data-scroll-to]'))) {
        scrollToField(element);
    }
});

document.addEventListener('mouseover', (event) => {
    const trigger = closest(event, '.info-hover-trigger');
    if (trigger) {
        infoHoverShow(trigger);
    } else if (closest(event, '.information-image')) {
        infoHoverCancelHide();
    }
});

document.addEventListener('mouseout', (event) => {
    if (closest(event, '.info-hover-trigger, .information-image')) {
        infoHoverScheduleHide();
    }
});

document.addEventListener('focusin', (event) => {
    const trigger = closest(event, '.info-hover-trigger');
    if (trigger) {
        infoHoverShow(trigger);
    }
});

document.addEventListener('focusout', (event) => {
    if (closest(event, '.info-hover-trigger')) {
        infoHoverScheduleHide();
    }
});

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        infoHoverHideAll();
    }
    const toggle = closest(event, '[data-triple-toggle]');
    if (toggle && (event.key === 'Enter' || event.key === ' ')) {
        event.preventDefault();
        tripleToggle(toggle);
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
