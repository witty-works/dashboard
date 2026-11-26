// Chart.js, its data labels plugin and moment for the analytics page, from
// the app's own bundle rather than a third-party CDN. The page's inline
// script uses them as globals.
import Chart from 'chart.js';
import 'chartjs-plugin-datalabels';
// vite.config.js resolves 'moment' to the same file the locale files load.
import moment from 'moment';
import 'moment/locale/de';
import 'moment/locale/fr';

// The locale files switch moment's locale as they load; use the page's.
moment.locale(document.documentElement.lang || 'en');

window.Chart = Chart;
window.moment = moment;
