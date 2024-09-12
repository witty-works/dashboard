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

import moment from 'moment';
import 'moment/locale/de'
import 'moment/locale/fr'
import {Chart} from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import FroalaEditor from 'froala-editor'
global.FroalaEditor = FroalaEditor;