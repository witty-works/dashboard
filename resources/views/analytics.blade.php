<?php
    $filter = isset($team) ? ['team_id' => $team->id] : ['user_id' => $user->id];
    $has_term_replacements = \App\Models\TermReplacement::where($filter)->exists();
    $dictionaryItems = isset($team)
        ? $team->getTotalTermReplacementsCount()
        : $user->getTotalTermReplacementsCount() + $user->currentTeam->getTotalTermReplacementsCount()
    ;
?>
<x-app-layout :pagetitle="__('content.analytics')">
<div class="wittyworks-navigation-wrapper">
    @livewire('navigation-menu')
</div>
<div class="wittyworks-page-wrapper" id="maincontent">
   <div class="wittyworks-page lg:ml-20">
      @include('partials.banners')
      <h1 class="ibarra-sub-title-h1 margin-top">
         {{ __('content.analytics') }}
      </h1>
      {{-- Plain text: it changes every 30 seconds, which a live region would read out each time.
           The button stays in place, so it keeps focus while the text changes. --}}
      <div class="lato-small-text-p container-row margin-top" style="align-items: center;">
         <span id="lastRefresh" class="wittyworks-margin-right" style="visibility: hidden;"></span>
         <button type="button" class="button primary-button-red" id="refresh-data" hidden>{{ __('content.refresh_data') }}</button>
      </div>
      <div class="container-row margin-top">
        <div class="drowdown-wrapper">

            <div class="container-row">
                <label class="lato-small-text-p wittyworks-margin-right" for="timerangeDropdown">{{ __('content.chart_time_range') }}</label>
            </div>
            @php
                $ranges = [
                    '1m' => __('content.chart_time_range_month'),
                    '3m' => __('content.chart_time_range_quarter'),
                    '1y' => __('content.chart_time_range_year'),
                ];
                $disabled = false;
            @endphp
            <x-select
                :options="$ranges"
                :disabled="$disabled"
                class="dropdown margin-right"
                id="timerangeDropdown"
            />
        </div>

        <div class="drowdown-wrapper">
            <label class="lato-small-text-p wittyworks-margin-right" for="languageDropdown">{{ __('content.language_filter') }}</label>

            @php
                $ranges = [
                    '' => __('content.language_filter_any'),
                    'en' => __('content.language_filter_en'),
                    'de' => __('content.language_filter_de'),
                    'fr' => __('content.language_filter_fr'),
                ];
            @endphp
            <x-select
                :options="$ranges"
                class="dropdown margin-right"
                id="languageDropdown"
            />
        </div>

        <div class="drowdown-wrapper">
            <label class="lato-small-text-p wittyworks-margin-right" for="inclusiveDropdown">{{ __('content.inclusive_filter') }}</label>
            @php
                $ranges = [
                    'non_inclusive' => __('content.inclusive_filter_non_inclusive'),
                    'both' => __('content.inclusive_filter_both'),
                    'inclusive' => __('content.inclusive_filter_inclusive'),
                ];
            @endphp
            <x-select
                :options="$ranges"
                class="dropdown margin-right"
                id="inclusiveDropdown"
            />
        </div>


        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right" id="category-filter-label">{{ __('content.category_filter') }}</div>
                <div class="toggle-next checkbox-wrapper">
                    <button type="button" class="ellipsis lato-small-text-p" aria-haspopup="true" aria-expanded="false" aria-controls="Categories" aria-describedby="category-filter-label">{{ __('content.all_categories') }}</button>
                    <div class="checkboxes" id="Categories">
                        <div class="inner-wrap">
                            @foreach ($categories as $category => $category_data)
                            <label>
                                <input type="checkbox" value="{{ $category }}" class="ckkBox val" checked/>
                                <span>{{ $category_data['translation']['hs_name'] }}</span>
                            </label><br>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>

      <div class="analytics-tab-container" role="tablist" aria-label="{{ __('content.analytics') }}">
         <button type="button" role="tab" aria-selected="true" aria-controls="overview" class="analytics-tab" id="overview-tab" style="color: #6357b8;">
            {{ __('content.analytic_overview') }}
            <span class="analytics-tab-line" id="overview-line" aria-hidden="true"></span>
        </button>
         <button type="button" role="tab" aria-selected="false" tabindex="-1" aria-controls="top-categories" class="analytics-tab" id="top-categories-tab">
            {{ __('content.top_categories') }}
            <span id="top-categories-line" aria-hidden="true"></span>
        </button>
         <button type="button" role="tab" aria-selected="false" tabindex="-1" aria-controls="top-words" class="analytics-tab" id="top-words-tab">
            {{ __('content.analytic_top_words') }}
            <span id="top-words-line" aria-hidden="true"></span>
        </button>
      </div>

      <!-- Overview content here -->
      <div id="overview" role="tabpanel" aria-labelledby="overview-tab">
         <div class="wittyworks-form-section container border-radius mt-5">
            <div id="loading-icon-overview" class="loading-icon-wrapper" role="status" style="width: 100%">
               <span class="sr-only">{{ __('content.loading') }}</span>
               <div class="lds-grid" aria-hidden="true">
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
                  <div></div>
               </div>
            </div>
            <div id="overview-no-data" style="visibility: hidden; width: 100%">
               <div class="lato-small-text-p warning-message">{!! __('content.no_data_events') !!}</div>
               <div class="image-container">
                  <img src="{{ url('svg/screenshots/overviewNoData.png') }}" alt="" class="image">
                  <div id="overview-no-data-text" class="centered-image-text"></div>
               </div>
            </div>
            <div id="overview-wrapper" style="visibility: hidden; width: 100%">
               <div class="container-row wittyworks-margin-top">
                  <div id="eventsChart-legend" class="chart-legend lato-small-text-p" role="group" aria-label="{{ __('content.chart_lines') }}"></div>
                  <canvas id="eventsChart" role="img" aria-label="{{ __('content.analytic_overview') }}" class="wittyworks-analytics-chart-extra-large"></canvas>
                  <details id="eventsChart-data" class="chart-data" hidden><summary class="lato-small-text-p">{{ __('content.chart_data_table') }}</summary><div class="chart-data-table"></div></details>
               </div>
            </div>
         </div>
      </div>

      <!-- Top categories content here -->
      <div id="top-categories" role="tabpanel" style="display: none;" aria-labelledby="top-categories-tab">
         <div id="top-categories-wrapper" style="width: 100%">
            <div class="wittyworks-form-section container border-radius mt-5">
                <div class="container-row wittyworks-margin-right">
                    <label class="lato-small-text-p wittyworks-margin-right" for="eventTypeTopCategories">{{ __('content.event_type') }}</label>
                    <div id="eventTypeDropdownTopCategories"></div>
                </div>
                <div id="loading-icon-top-categories" class="loading-icon-wrapper" role="status" style="width: 100%">
                    <span class="sr-only">{{ __('content.loading') }}</span>
                    <div class="lds-grid" aria-hidden="true">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
               </div>
               <div id="top-categories-no-data" style="visibility: hidden; width: 100%">
                    <div class="lato-small-text-p warning-message">{!! __('content.no_data_events') !!}</div>
                    <div class="image-container">
                        <img src="{{ url('svg/screenshots/topCategoriesNoData.png') }}" alt="" class="image">
                        <div id="top-categories-no-data-text" class="centered-image-text"></div>
                    </div>
               </div>
               <div id="top-categories-content" style="visibility: hidden; width: 100%">
                    <div class="chart-container-row">
                        <canvas id="topSubCategoriesBarChart" role="img" aria-label="{{ __('content.top_categories') }}" class="wittyworks-analytics-chart-extra-large"></canvas>
                        <details id="topSubCategoriesBarChart-data" class="chart-data" hidden><summary class="lato-small-text-p">{{ __('content.chart_data_table') }}</summary><div class="chart-data-table"></div></details>
                    </div>
                    <div class="chart-container-row">
                        <div id="topSubCategoriesChart-legend" class="chart-legend lato-small-text-p" role="group" aria-label="{{ __('content.chart_lines') }}"></div>
                        <canvas id="topSubCategoriesChart" role="img" aria-label="{{ __('content.top_categories') }}" class="wittyworks-analytics-chart-extra-large"></canvas>
                        <button type="button" id="toggleLines" class="button primary-button-red">{{ __('content.toggle_lines') }}</button>
                        <details id="topSubCategoriesChart-data" class="chart-data" hidden><summary class="lato-small-text-p">{{ __('content.chart_data_table') }}</summary><div class="chart-data-table"></div></details>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Top words content here -->
      <div id="top-words" role="tabpanel" style="display: none;" aria-labelledby="top-words-tab">
         <div id="top-words-wrapper" style="width: 100%">
            <div class="wittyworks-form-section container border-radius mt-5">
                <div class="container-row wittyworks-margin-right">
                    <label class="lato-small-text-p wittyworks-margin-right" for="eventTypeTopWords">{{ __('content.event_type') }}</label>
                    <div id="eventTypeDropdownTopWords"></div>
                </div>
                <div id="loading-icon-top-words" class="loading-icon-wrapper" role="status" style="width: 100%">
                    <span class="sr-only">{{ __('content.loading') }}</span>
                    <div class="lds-grid" aria-hidden="true">
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                        <div></div>
                    </div>
                </div>
               <div id="top-words-no-data" style="visibility: hidden; width: 100%">
                    <div class="lato-small-text-p warning-message">{!! __('content.no_data_events') !!}</div>
                    <div class="image-container">
                        <img src="{{ url('svg/screenshots/topWordsNoData.png') }}" alt="" class="image">
                        <div id="top-words-no-data-text" class="centered-image-text"></div>
                    </div>
               </div>
               <div id="top-words-content" style="visibility: hidden; width: 100%">
                    <div class="chart-container-row">
                        <canvas
                            id="topWordsChart"
                            role="img" aria-label="{{ __('content.analytic_top_words') }}"
                            class="wittyworks-analytics-chart-extra-large wittyworks-margin-top">
                        </canvas>
                        <div class="chart-footer" id="topWordsChartFooter"></div>
                        <details id="topWordsChart-data" class="chart-data" hidden><summary class="lato-small-text-p">{{ __('content.chart_data_table') }}</summary><div class="chart-data-table"></div></details>
                    </div>
                    <div class="chart-container-row wittyworks-margin-top">
                        <canvas
                            id="topWordsChartCorporateRules"
                            role="img" aria-label="{{ __('content.analytic_top_words') }}"
                            class="wittyworks-analytics-chart-extra-large">
                        </canvas>
                        <div class="chart-footer" id="topWordsChartCorporateRulesFooter"></div>
                        <details id="topWordsChartCorporateRules-data" class="chart-data" hidden><summary class="lato-small-text-p">{{ __('content.chart_data_table') }}</summary><div class="chart-data-table"></div></details>
                    </div>
                  <div id="noCorporateRulesWrapper" class="container-row wittyworks-margin-top" style="display: none;">
                     <div class="lato-small-text-p">{!! empty($user) ? __('content.no_corporate_rules', ['url' => e(route('teams.dictionary'))]) : __('content.no_corporate_rules_user', ['url' => e(route('user.dictionary'))]) !!}</div>
                  </div>
                  <div id="corporateRulesButNoneOpened" class="container-row wittyworks-margin-top" style="display: none;">
                     <div class="lato-small-text-p">{!! empty($user) ? __('content.corporate_rules_none_opened', ['url' => e(route('teams.dictionary'))]) : __('content.corporate_rules_none_opened_user', ['url' => e(route('user.dictionary'))]) !!}</div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</x-app-layout>

{{-- Chart and moment come from the app's bundle, which runs deferred: before DOMContentLoaded, after this page's inline script. --}}
@vite('resources/js/analytics.js')

<script @cspNonce>
    let topSubChart;
    const setElementStyle = (elementId, property, value) => {
        const element = document.getElementById(elementId);
        if (element) {
            element.style[property] = value;
        }
    };


    const chartTexts = {
        period: @json(__('content.chart_table_period')),
        name: @json(__('content.chart_table_name')),
        count: @json(__('content.chart_table_count')),
    };

    // Lines differ in their dash and point shape too, not only in colour.
    const lineDashes = [[], [8, 4], [2, 3], [10, 3, 2, 3]];
    const pointStyles = ['circle', 'rect', 'triangle', 'rectRot', 'star', 'crossRot', 'rectRounded', 'cross'];
    function styleLines(datasets) {
        datasets.forEach((dataset, i) => {
            dataset.borderDash = lineDashes[i % lineDashes.length];
            dataset.pointStyle = pointStyles[i % pointStyles.length];
            dataset.pointRadius = 4;
        });
    }

    // Only web links from the API become links.
    function safeUrl(url) {
        try {
            const parsed = new URL(url, window.location.href);
            return ['http:', 'https:'].includes(parsed.protocol) ? parsed.href : null;
        } catch (e) {
            return null;
        }
    }

    // The chart's data as a table, below the chart: what a screen reader or
    // anyone who cannot read the chart uses instead.
    function describeChart(chart) {
        const details = document.getElementById(chart.canvas.id + '-data');
        if (!details) return;
        const isBar = chart.config.type === 'bar';
        const datasets = isBar ? chart.data.datasets.slice(0, 1) : chart.data.datasets;
        const table = document.createElement('table');
        const head = table.createTHead().insertRow();
        const header = (cell, text, url = null) => {
            cell.scope = 'col';
            if (url) {
                const link = document.createElement('a');
                link.href = url;
                link.target = '_blank';
                link.rel = 'noopener';
                link.textContent = text;
                cell.appendChild(link);
            } else {
                cell.textContent = text;
            }
            return cell;
        };
        head.appendChild(header(document.createElement('th'), isBar ? chartTexts.name : chartTexts.period));
        datasets.forEach((dataset) => head.appendChild(
            header(document.createElement('th'), isBar ? chartTexts.count : dataset.label, isBar ? null : safeUrl(dataset.url))
        ));
        const body = table.createTBody();
        chart.data.labels.forEach((label, i) => {
            const row = body.insertRow();
            const th = document.createElement('th');
            th.scope = 'row';
            th.textContent = Array.isArray(label) ? label.join(' ') : String(label);
            row.appendChild(th);
            datasets.forEach((dataset) => {
                row.insertCell().textContent = dataset.data[i] ?? '';
            });
        });
        details.querySelector('.chart-data-table').replaceChildren(table);
        details.hidden = false;
    }

    // A legend of checkboxes instead of the canvas one, which only a mouse
    // can use: each switches its line on and off.
    function renderLegend(chart) {
        const box = document.getElementById(chart.canvas.id + '-legend');
        if (!box) return;
        box.replaceChildren(...chart.data.datasets.map((dataset, i) => {
            const label = document.createElement('label');
            label.className = 'chart-legend-item';
            const input = document.createElement('input');
            input.type = 'checkbox';
            input.checked = chart.isDatasetVisible(i);
            input.addEventListener('change', () => {
                chart.setDatasetVisibility(i, input.checked);
                chart.update();
            });
            const sample = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            sample.setAttribute('width', '28');
            sample.setAttribute('height', '10');
            sample.setAttribute('aria-hidden', 'true');
            const line = document.createElementNS('http://www.w3.org/2000/svg', 'line');
            line.setAttribute('x1', '0');
            line.setAttribute('y1', '5');
            line.setAttribute('x2', '28');
            line.setAttribute('y2', '5');
            line.setAttribute('stroke', dataset.borderColor);
            line.setAttribute('stroke-width', '3');
            if (dataset.borderDash && dataset.borderDash.length) {
                line.setAttribute('stroke-dasharray', dataset.borderDash.join(' '));
            }
            sample.appendChild(line);
            const text = document.createElement('span');
            text.textContent = dataset.label;
            label.append(input, sample, text);
            return label;
        }));
    }

    function load_charts(refresh, chartType = 'overview', timerange = '1m', interval = 'week', language = [], categories = [], inclusive = 'non_inclusive', eventTypes = null) {
        // A tab or filter used before the chart bundle has run waits for it.
        if (!window.Chart) {
            const args = arguments;
            document.addEventListener('DOMContentLoaded', () => load_charts(...args));
            return;
        }
        if (eventTypes === null) {
            eventTypes = [@json($default_event)]
        }

        // At least 3:1 against white (WCAG 1.4.11), keeping each colour's hue.
        const colors = ['#eb6264', '#e8636f', '#e7647a', '#e46585', '#e2668f', '#dd6697', '#db67a1', '#d968aa', '#d769b3', '#d26aba',
 '#ce6dbc', '#c86fc0', '#c272c3', '#bc76c6', '#b678c9', '#af7bcc', '#a87dcd', '#a17fd0', '#9a82d3', '#9284d4',
 '#8689d3', '#798dcf', '#6b91ca', '#5e94c4', '#5196bf', '#4598b9', '#3b9bb5', '#309caf', '#299caa', '#2c9ca6',
 '#319ea4', '#349ea1', '#379e9c', '#3a9e98', '#3e9d94', '#419d90', '#449d8c'];

        const xValuesCheck = [];
        const yValuesCheck = [];

        const xValuesPopoverOpen = [];
        const yValuesPopoverOpen = [];

        const xValuesIgnore = [];
        const yValuesIgnore = [];

        const xValuesAlternative = [];
        const yValuesAlternative = [];

        const xValuesLearningBites = [];
        const yValuesLearningBites = [];

        const xValuesCheckResult = [];
        const yValuesCheckResult = [];

        const xTopSubCategoriesWeek = [];
        const yTopSubCategoriesWeek = [];

        const xValuesTopSubCategories = [];
        const yValuesTopSubCategories = [];

        const xValuesTopWords = [];
        const yValuesTopWords = [];

        const xValuesTopCorporateWords = [];
        const yValuesTopCorporateWords = [];

        // Chart.js 4: black text as before, and the curved lines Chart.js 2
        // drew by default.
        Chart.defaults.color = '#000000';
        Chart.defaults.elements.line.tension = 0.4;

        function formatChartLabels(xValues, yValues, interval) {
            const formattedXValues = [];

            if (interval == 'week') {
                for (var i = 0; i < xValues.length; i++) {
                    formattedXValues[i] = '{{ __('content.week') }} ' + moment(xValues[i]).startOf('week').isoWeekday(1).week() + ' ' + moment(xValues[i]).startOf('week').isoWeekday(1).year();
                }
            } else if (interval == 'month') {
                for (var i = 0; i < xValues.length; i++) {
                    formattedXValues[i] = moment(xValues[i]).format('MMMM YYYY');
                }
            }

            return [formattedXValues, yValues];
        }

        function handleNoData(loadingIconId, sectionIdNoData = null, chartId = null, isApiError) {
            setElementStyle(loadingIconId, 'display', 'none');
            if (sectionIdNoData) {
                setElementStyle(sectionIdNoData, 'visibility', 'visible');
                setElementStyle(sectionIdNoData, 'display', 'block');
            } else if (chartId) {
                setElementStyle(chartId, 'display', 'none');
            }
            const textElementId = sectionIdNoData ? sectionIdNoData + '-text' : null;
            const textElement = textElementId ? document.getElementById(textElementId) : null;
            if (textElement) {
                if (isApiError) {
                    textElement.innerHTML = @json(__('content.no_data_image_text_temp_unavailable'));
                } else {
                    textElement.innerHTML = @json(__('content.no_data_image_text'));
                }
            }
        }

        async function getChartData(chart, from = '1w', interval = 'day', language, categories, subcategories = [], to = null, inclusive, eventTypes = []) {
            let analyticsUrl = '/api/user/analytics?refresh=' + refresh + '&chart=';
            if (window.location.href.includes('team')) {
                analyticsUrl = '/api/team/analytics?refresh=' + refresh + '&chart=';
            }

            let formattedCategories = '';
            if (categories && categories.length > 0) {
                formattedCategories = '&' + categories.map(category => 'categories[]=' + category).join('&');
            }

            let formattedSubcategories = '';
            if (subcategories && subcategories.length > 0) {
                formattedSubcategories = '&' + subcategories.map(category => 'subcategories[]=' + category).join('&');
            }
            let formattedEventTypes = '';
            if (eventTypes && eventTypes.length > 0) {
                formattedEventTypes = '&' + eventTypes.map(event => 'events[]=' + event).join('&');
            }

            analyticsUrl += chart + '&from=' + from + '&interval=' + interval + '&lang=' + language + '&inclusive=' + inclusive + formattedCategories + formattedSubcategories + formattedEventTypes;

            try {
                const response = await fetch(analyticsUrl, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-App-Locale': '{{ app()->getLocale() }}',
                    },
                });
                if (response.status >= 500) {
                    return {data: null, errorStatus: response.status};
                }
                const data = await response.json();
                return data;
            } catch (e) {
                return {data: null, errorStatus: e.status};
            }
        }

        function generateBarChart(chartId, xValues, yValues, text, display, singeColor, data, calledDirectly = false) {
            const chartElement = document.getElementById(chartId);
            if (chartElement) {
                chartElement.style.display = 'flex';
                const ctx = chartElement.getContext('2d');

                // Chart.js 4 refuses a second chart on a canvas; a filter
                // change draws this one again.
                Chart.getChart(chartElement)?.destroy();
                const barChart = new Chart(ctx, {
                    type: "bar",
                    data: {
                        labels: xValues,
                        datasets: [{
                            data: yValues,
                            backgroundColor: singeColor ? colors[10] : colors,
                            fill: false,
                        }]
                    },
                    options: {
                        events: [],
                        maintainAspectRatio: false,
                        responsive: true,
                        scales: {
                            x: {
                                display: display,
                                ticks: {
                                    // Avoid very long labels. A category axis passes the
                                    // tick's index; the label is looked up from it.
                                    callback: function(value) {
                                        const label = String(this.getLabelForValue(value));
                                        return label.length > 20 ? label.substring(0, 20) + '...' : label;
                                    }
                                }
                            },
                            y: {
                                beginAtZero: true,
                                min: 0,
                                max: Math.max(...yValues) + Math.max(...yValues) * 0.15,
                                ticks: {
                                    callback: function(value) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            },
                        },
                        plugins: {
                            title: {
                                display: true,
                                text: text,
                                font: {size: 16, weight: 'normal', family: 'Lato'},
                            },
                            legend: {
                                display: false
                            },
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                rotation: 315,
                                formatter: (currentValue, context) => {
                                    if (calledDirectly) return '';
                                    // The bar's own index: two bars with the same value
                                    // each get their own label.
                                    const index = context.dataIndex;
                                    const currentLabel = xValues[index];
                                    const eventTypeTopWords = document.getElementById("eventTypeTopWords").value;
                                    if (data.events && Object.hasOwn(data.events, eventTypeTopWords)) {
                                        const previousPeriodPopoverOpened = data.events?.[eventTypeTopWords];
                                        if (previousPeriodPopoverOpened[currentLabel]) {
                                            const diff = currentValue - previousPeriodPopoverOpened[currentLabel];
                                            const percentage = (diff / previousPeriodPopoverOpened[currentLabel] * 100).toFixed(0);
                                            if (percentage == 0) {
                                                return '+ 100 %';
                                            }

                                            return percentage >= 0 ? '+' + percentage + '%' : percentage + '%';
                                        }
                                    }
                                    return '+ 100 %';
                                },
                            }
                        },
                    }
                });
                describeChart(barChart);
            }
        }

        function createComparisonBarChart(chartId, xValues, yValues, text, display, singeColor, chart, from, interval, language, categories, subcategories = []) {
            if (yValues.every((val) => val === 0)) {
                setElementStyle(document.getElementById(chartId), 'display', 'none');
                return;
            }

            const newFrom = from.slice(0, 1) * 2 + from.slice(1, 2);
            const to = from;

            // Assuming today's date for the end of the current period
            const currentDate = new Date();
            const endDateCurrentPeriod = formatDate(currentDate);

            function formatDate(date) {
                const day = String(date.getDate()).padStart(2, '0');
                const month = String(date.getMonth() + 1).padStart(2, '0'); // +1 because months are 0-based in JavaScript
                const year = date.getFullYear();
                return `${day}.${month}.${year}`;
            }

            let startDateCurrentPeriod;
            switch(from) {
                case '1m':
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    break;
                case '3m':
                    currentDate.setMonth(currentDate.getMonth() - 3);
                    break;
                case '1y':
                    currentDate.setFullYear(currentDate.getFullYear() - 1);
                    break;
            }
            startDateCurrentPeriod = formatDate(currentDate);

            // For comparison periods, assuming you want to compare with the previous interval of the same length
            const endDateComparePeriod = startDateCurrentPeriod;

            switch(from) {
                case '1m':
                    currentDate.setMonth(currentDate.getMonth() - 1);
                    break;
                case '3m':
                    currentDate.setMonth(currentDate.getMonth() - 3);
                    break;
                case '1y':
                    currentDate.setFullYear(currentDate.getFullYear() - 1);
                    break;
            }
            const startDateComparePeriod = formatDate(currentDate);

            const footerText = `{{ __('content.chart_period_comparison_from') }} ${startDateCurrentPeriod} - ${endDateCurrentPeriod}, {{ __('content.chart_period_comparison_to') }} ${startDateComparePeriod} - ${endDateComparePeriod}`;
            const footerElement = document.getElementById(`${chartId}Footer`);
            if (footerElement) {
                footerElement.innerText = footerText;
            }

            getChartData(chart, newFrom, interval, language, categories, subcategories = [], to, inclusive, eventTypes).then(data => {
                generateBarChart(chartId, xValues, yValues, text, display, singeColor, data);
            });
        }

        chartType == 'overview' && getChartData('total', timerange, interval, language, categories, null,  null, inclusive, ['check_highlights','popover_open','alternative','ignore','learning_bites']).then(data => { //TODO
            if (data.errorStatus === 503) {
                handleNoData('loading-icon-overview', 'overview-no-data', '', true);
                return;
            }
            const showCheckHighlights=
                @json($default_event) !== 'popover_open' &&
                data.events?.check_highlights &&
                Object.entries(data.events.check_highlights).filter(([key, value]) => value > 0).length > 0 //could adjust this to a min amount of check highlights
            
            if (
                (!showCheckHighlights&& !data.events?.popover_open) ||
                (!showCheckHighlights&& Object.entries(data.events?.popover_open).filter(([key, value]) => value > 0).length == 0)) {
                handleNoData('loading-icon-overview', 'overview-no-data', '', false);
                return;
            }
            const events = data.events || {};
            const writing_streak = data.writing_streak || 0;
            updateLastRefresh(data.last_refresh);

            for (const [event, value] of Object.entries(events)) {
                if (event === 'popover_open') {
                for (const [date, count] of Object.entries(value)) {
                        xValuesPopoverOpen.push(date);
                        yValuesPopoverOpen.push(count);
                    }
                } else if (event === 'ignore') {
                    for (const [date, count] of Object.entries(value)) {
                        xValuesIgnore.push(date);
                        yValuesIgnore.push(count);
                    }
                } else if (event === 'alternative') {
                    for (const [date, count] of Object.entries(value)) {
                        xValuesAlternative.push(date);
                        yValuesAlternative.push(count);
                    }
                } else if (event === 'learning_bites') {
                    for (const [date, count] of Object.entries(value)) {
                        xValuesLearningBites.push(date);
                        yValuesLearningBites.push(count);
                    }
                } else if (event === 'check_highlights') {
                    for (const [date, count] of Object.entries(value)) {
                        xValuesCheckResult.push(date);
                        yValuesCheckResult.push(count);
                    }
                }

                setElementStyle('lastRefresh', 'visibility', 'visible');
                const aggregatedPopoverOpenData = formatChartLabels(xValuesPopoverOpen, yValuesPopoverOpen, interval)[1];
                const aggregatedAlternativeData = formatChartLabels(xValuesPopoverOpen, yValuesAlternative, interval)[1];
                const aggregatedIgnoreData = formatChartLabels(xValuesPopoverOpen, yValuesIgnore, interval)[1];
                const aggregatedLearningBitesData = formatChartLabels(xValuesPopoverOpen, yValuesLearningBites, interval)[1];
                const aggregatedCheckResultData = formatChartLabels(xValuesPopoverOpen, yValuesCheckResult, interval)[1];

                if (aggregatedLearningBitesData.every(Number.isInteger)) {
                    var datasetsOverview = [
                        {
                            data: aggregatedPopoverOpenData,
                            borderColor: colors[8],
                            fill: false,
                            label: "{{ __('content.popover_label_line_chart') }}",
                            hidden: showCheckHighlights,
                        },
                        {
                            data: aggregatedAlternativeData,
                            borderColor: colors[16],
                            fill: false,
                            label: "{{ __('content.alternative_label_line_chart') }}",
                            hidden: showCheckHighlights,
                        },
                        {
                            data: aggregatedIgnoreData,
                            borderColor: colors[24],
                            fill: false,
                            label:  "{{ __('content.ignored_label_line_chart') }}",
                            hidden: showCheckHighlights,
                        },
                        {
                            data: aggregatedLearningBitesData,
                            borderColor: colors[32],
                            fill: false,
                            label:  @json(__('content.learning_bites_label_line_chart')),
                            hidden: showCheckHighlights,
                        },
                    ];

                    if (@json($default_event) !== 'popover_open') {
                        datasetsOverview.unshift(
                            {
                                data: aggregatedCheckResultData,
                                borderColor: colors[0],
                                fill: false,
                                label: @json(__('content.check_highlights_label_line_chart')) +
                                    (!showCheckHighlights
                                    ? ' ({{ __('teams.not_enough_data_to_display') }})'
                                    : ''),
                                hidden: !showCheckHighlights,
                            }
                        );
                    }


                    ctx = document.getElementById('eventsChart').getContext('2d');
                    // Drawn again for each event type and on every filter change;
                    // Chart.js 4 refuses a second chart on the canvas.
                    Chart.getChart('eventsChart')?.destroy();
                    styleLines(datasetsOverview);
                    const overviewChart = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels: formatChartLabels(xValuesPopoverOpen, null, interval)[0],
                            datasets: datasetsOverview,
                        },
                        options: {
                            plugins: {
                                tooltip: {
                                    enabled: true,
                                    mode: 'nearest',
                                    intersect: true,
                                    backgroundColor: '#f5f5f5',
                                    titleColor: 'black',
                                    bodyColor: 'black',
                                    callbacks: {
                                        title: function() {
                                            return '';
                                        },
                                        label: function(context) {
                                            return context.dataset.label + ': ' + context.formattedValue;
                                        },
                                        labelColor: function() {
                                            return {
                                                borderColor: 'rgba(0,0,0,0)',
                                                backgroundColor: 'rgba(0,0,0,0)'
                                            };
                                        },
                                        afterLabel: function() {
                                            return '';
                                        }
                                    },
                                    displayColors: false
                                },
                                // An HTML legend (renderLegend) replaces the canvas one.
                                legend: {display: false},
                                title: {
                                    display: true,
                                    text:  @json(__('content.analytic_overview')),
                                    font: {size: 16, weight: 'normal'},
                                },
                                datalabels: {
                                    display: false,
                                }
                            },
                            onHover: function(e, elements) {
                                e.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                            },
                            events: ['click', 'mousemove', 'mouseout'],
                            maintainAspectRatio: false,
                            responsive: true,
                            scales: {
                                y: {
                                    min: 0,
                                    ticks: {
                                        callback: function(value) {
                                            if (Math.floor(value) === value) {
                                                return value;
                                            }
                                        }
                                    }
                                },
                            },
                        }
                    });
                    renderLegend(overviewChart);
                    describeChart(overviewChart);
                }
            setElementStyle('loading-icon-overview', 'display', 'none');
            setElementStyle('overview-no-data', 'display', 'none');
            setElementStyle('overview-wrapper', 'visibility', 'visible');
            }
        });

        (chartType == 'top-categories') && getChartData('topSubcategories', timerange, interval, language, categories, null,  null, inclusive, eventTypes).then(data => {
            const selectedDropdownValue = eventTypes[0] || @json($default_event);
            const eventTypeDropdownTopCategories = document.getElementById("eventTypeDropdownTopCategories");
            eventTypeDropdownTopCategories.innerHTML = `
                <select id="eventTypeTopCategories" class="dropdown" aria-label="{{ __('content.event_type') }}">
                    ${getOptionsHtml()}
                </select>`;

            updateDropdown("eventTypeTopCategories", selectedDropdownValue);

            if (data.errorStatus === 503) {
                handleNoData('loading-icon-top-categories', 'top-categories-no-data', 'topSubcategories', true);
                return;
            }
            if (!data.events) {
                handleNoData('loading-icon-top-categories', 'top-categories-no-data', 'topSubcategories', false);
                return;
            }

            let categories = [];
            let datasetsLineChart = [];
            const stepSize = (colors.length - 1) / (Object?.keys(data.events[eventTypes[0]]).length - 1);
            updateLastRefresh(data.last_refresh);

            for (const [key, value] of Object.entries(data.events[eventTypes[0]])) {
                if (Object?.keys(data.events[eventTypes[0]]).indexOf(key) === 15) break;
                if (!value.counts) continue;
                const index = Object?.keys(data.events[eventTypes[0]]).indexOf(key);
                const colorIndex = Math.round(index * stepSize);
                const borderColor = colors[colorIndex];
                categories.push(value.name);

                datasetsLineChart.push({
                    data: Object.values(value.counts),
                    borderColor: borderColor,
                    fill: false,
                    label: value.name,
                    hidden: false,
                    url: value.url,
                    pointHitRadius: 20,
                });
            }

            generateBarChart(
                "topSubCategoriesBarChart",
                datasetsLineChart.map(dataset => dataset.label),
                datasetsLineChart.map(dataset => dataset.data.reduce((a, b) => a + b, 0)),
                "{{ __('content.analytic_top_categories') }}",
                true,
                false,
                'topSubcategories',
                timerange,
                interval,
                language,
                categories,
                eventTypes,
                true
            );

            if (topSubChart) {
                topSubChart.destroy();
            }
            ctx = document.getElementById('topSubCategoriesChart').getContext('2d');
            styleLines(datasetsLineChart);
            topSubChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Object?.keys(data.events[eventTypes[0]][Object?.keys(data.events[eventTypes[0]])[0]].counts),
                    datasets: datasetsLineChart,
                    },
                    options: {
                        plugins: {
                            tooltip: {
                                enabled: true,
                                mode: 'nearest',
                                intersect: true,
                                backgroundColor: '#f5f5f5',
                                titleColor: 'black',
                                bodyColor: 'black',
                                callbacks: {
                                    title: function() {
                                        return '';
                                    },
                                    label: function(context) {
                                        return context.dataset.label;
                                    },
                                    labelColor: function() {
                                        return {
                                            borderColor: 'rgba(0,0,0,0)',
                                            backgroundColor: 'rgba(0,0,0,0)'
                                        };
                                    },
                                    afterLabel: function() {
                                        return '';
                                    }
                                },
                                displayColors: false
                            },
                            // An HTML legend (renderLegend) replaces the canvas one.
                            legend: {display: false},
                            title: {
                                display: true,
                                text:  @json(__('content.top_categories_over_time')),
                                font: {size: 16, weight: 'normal'},
                            },
                            datalabels: {
                                display: false,
                            }
                        },
                        // A click on a line opens its category's page.
                        onClick: function(e, elements, chart) {
                            const line = elements[0];
                            if (!line) return;
                            window.open(chart.data.datasets[line.datasetIndex].url, '_blank');
                        },
                        onHover: function(e, elements) {
                            e.native.target.style.cursor = elements.length ? 'pointer' : 'default';
                        },
                        events: ['click', 'mousemove', 'mouseout'],
                        maintainAspectRatio: false,
                        responsive: true,
                        scales: {
                            x: {
                                ticks: {
                                    autoSkip: true,
                                    minRotation: 30
                                }
                            },
                            y: {
                                min: 0,
                                ticks: {
                                    callback: function(value) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            },
                        },
                    }
                });
            renderLegend(topSubChart);
            describeChart(topSubChart);
            const toggleLinesButton = document.getElementById('toggleLines');
            toggleLinesButton.removeEventListener('click', toggleLines);
            toggleLinesButton.addEventListener('click', toggleLines);

            setElementStyle('loading-icon-top-categories', 'display', 'none');
            setElementStyle('top-categories-content', 'visibility', 'visible');
            setElementStyle('top-categories-content', 'display', 'block');
        });

        chartType == 'top-words' && getChartData('topWords', timerange, interval, language, categories, null, null, inclusive, eventTypes).then(data => {
            const selectedDropdownValue = eventTypes[0] || @json($default_event);
            const eventTypeDropdownTopWords = document.getElementById("eventTypeDropdownTopWords");
            eventTypeDropdownTopWords.innerHTML = `
                <select id="eventTypeTopWords" class="dropdown" aria-label="{{ __('content.event_type') }}">
                    ${getOptionsHtml()}
                </select>`;
            updateDropdown("eventTypeTopWords", selectedDropdownValue);

            if (data.errorStatus === 503) {
                handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords', true);
                return;
            }

            if (!data.events ) {
                handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords', false);
                return;
            }

            updateLastRefresh(data.last_refresh);

            const events = data.events[eventTypes[0]] || {};
            for (const [key, value] of Object.entries(events)) {
                if (!key || !value) continue;
                if (isNaN(key)) {
                    xValuesTopWords.push(key);
                    yValuesTopWords.push(value);
                }
            }

            if (xValuesTopWords.length === 0) {
                handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords');
                return;
            }

            const xValuesTopWordsCut = xValuesTopWords.slice(0, 15);
            const yValuesTopWordsCut = yValuesTopWords.slice(0, 15);

            createComparisonBarChart(
                "topWordsChart",
                xValuesTopWordsCut,
                yValuesTopWordsCut,
                "{{ __('content.analytic_top_words') }}",
                true,
                false,
                'topWords',
                timerange,
                interval,
                language,
                categories,
                ['corporate_rules']
            );

            setElementStyle('loading-icon-top-words', 'display', 'none');
            setElementStyle('top-words-content', 'visibility', 'visible');
            setElementStyle('top-words-content', 'display', 'block');
        });

        chartType == 'top-words' && getChartData('topWords', timerange, 'day', language, categories, ['corporate_rules'], null, inclusive, eventTypes).then(data => {
            if (data && data.events) {
                const corporatewords = data.events[eventTypes[0]] || {};
                for (const [key, value] of Object.entries(corporatewords)) {
                    if (!key || !value) continue;
                    xValuesTopCorporateWords.push(key);
                    yValuesTopCorporateWords.push(value);
                }

                if (xValuesTopCorporateWords.length === 0) {
                    return;
                }

                const xValuesTopCorporateWordsCut = xValuesTopCorporateWords.slice(0, 15);
                const yValuesTopCorporateWordsCut = yValuesTopCorporateWords.slice(0, 15);
                createComparisonBarChart(
                    "topWordsChartCorporateRules",
                    xValuesTopCorporateWordsCut,
                    yValuesTopCorporateWordsCut,
                    "{{ __('content.analytic_top_words_corporate_rules') }}",
                    true,
                    false,
                    'topWords',
                    timerange,
                    'day',
                    language,
                    categories,
                    ['corporate_rules']
                );
                setElementStyle('loading-icon-top-words', 'display', 'none');
                setElementStyle('top-words-content', 'visibility', 'visible');
                setElementStyle('top-words-content', 'display', 'block');
            } else {
                if ({{ $dictionaryItems}}) {
                    setElementStyle('corporateRulesButNoneOpened', 'display', 'flex');
                } else {
                    setElementStyle('noCorporateRulesWrapper', 'display', 'flex');
                }
            }
        });
    };

    function toggleLines() {
        const allVisible = topSubChart.data.datasets.every((dataset, i) => topSubChart.isDatasetVisible(i));
        topSubChart.data.datasets.forEach((dataset, i) => topSubChart.setDatasetVisibility(i, !allVisible));
        topSubChart.update();
        renderLegend(topSubChart);
    }

    function updateDropdown(dropdownId, dropdownValue) {
        const dropdown = document.getElementById(dropdownId);
        if (!dropdown) return;
        const dropdownOptions = dropdown.options;
        for (let i = 0; i < dropdownOptions.length; i++) {
            if (dropdownOptions[i]?.value == dropdownValue) {
                dropdownOptions[i].selected = true;
            }
        }
    }

    function setParams(eventTypes = null, refresh = false) {
        if (eventTypes === null) {
            eventTypes = [@json($default_event)]

            const selectedDropdownValue = eventTypes[0] || @json($default_event);
            updateDropdown("eventTypeTopCategories", selectedDropdownValue);
            updateDropdown("eventTypeTopWords", selectedDropdownValue);
        }

        const activeTab = document.getElementsByClassName("analytics-tab-line")[0].id.replace('-line', '');

        setElementStyle('noCorporateRulesWrapper', 'display', 'none');
        setElementStyle('corporateRulesButNoneOpened', 'display', 'none');

        if (activeTab) {
            setElementStyle('loading-icon-' + activeTab, 'display', 'flex');
            setElementStyle('loading-icon-' + activeTab, 'visibility', 'visible');
            setElementStyle(activeTab + '-no-data', 'display', 'none');

            if (activeTab === 'overview' && document.getElementById(activeTab + '-wrapper')){
                setElementStyle(activeTab + '-wrapper', 'visibility', 'hidden');
            } else if (document.getElementById(activeTab + '-content')) {
                setElementStyle(activeTab + '-content', 'visibility', 'hidden');
            }
        }

        const categories = getSelectedCategories();
        const timeRangeDropdown = document.getElementById("timerangeDropdown");
        const languageDropdown = document.getElementById("languageDropdown");
        const inclusiveDropdown = document.getElementById("inclusiveDropdown");

        const selectedTimeRangeOption = timeRangeDropdown.options[timeRangeDropdown.selectedIndex]?.value;
        const selectedLanguageOption = languageDropdown.options[languageDropdown.selectedIndex]?.value;
        const selectedInclusiveOption = inclusiveDropdown.options[inclusiveDropdown.selectedIndex]?.value;

        const interval = selectedTimeRangeOption == '1y' ? 'month' : 'week';
        load_charts(refresh, activeTab, selectedTimeRangeOption, interval, selectedLanguageOption, categories, selectedInclusiveOption, eventTypes);
    }

    document.addEventListener('DOMContentLoaded', () => load_charts(false));

    // Listeners in place of inline handlers, which the Content-Security-Policy
    // blocks. On the document, since the event type menus and the refresh
    // button are rendered later.
    document.addEventListener('change', (event) => {
        if (['timerangeDropdown', 'languageDropdown', 'inclusiveDropdown'].includes(event.target.id)) {
            setParams();
        } else if (['eventTypeTopCategories', 'eventTypeTopWords'].includes(event.target.id)) {
            setParams([event.target.value]);
        }
    });

    document.addEventListener('click', (event) => {
        const tab = event.target.closest('.analytics-tab');
        if (tab) {
            handleTabClick(tab.getAttribute('aria-controls'));
        } else if (event.target.closest('#refresh-data')) {
            setParams(null, true);
            updateLastRefresh();
        }
    });

    //updateSection function
    function updateLastRefresh(lastRefresh = null) {
        if (lastRefresh === null) {
            lastRefresh = new Date().toJSON();
        }

        const element = document.getElementById("lastRefresh");
        element.setAttribute('last_refresh', lastRefresh);

        updateSection();
    }

    function updateSection() {
        const element = document.getElementById("lastRefresh");
        const lastRefresh = element.getAttribute('last_refresh');
        if (!lastRefresh) return;

        // Text only: the button next to it stays, and keeps focus.
        element.textContent = @json(__('content.last_refreshed')) + '\u00a0' + moment(lastRefresh).fromNow();

        const lastRefreshMinutes = Math.floor((new Date() - new Date(lastRefresh)) / 60000);
        document.getElementById('refresh-data').hidden = lastRefreshMinutes < 3;
    };

    setInterval(function() {
        updateSection()
    }, 30000);

    function getOptionsHtml() {
        var options = new Object();
        var optionsDisabled = new Object();
        if (@json($default_event) !== 'popover_open') {
            options['check_highlights'] = @json(__('content.check_highlights_label_line_chart'));
            optionsDisabled['check_highlights'] = '';
        }
        options['popover_open'] = @json(__('content.popover_label_line_chart'));
        optionsDisabled['popover_open'] = '';
        options['alternative'] = @json(__('content.alternative_label_line_chart'));
        optionsDisabled['alternative'] = '';
        options['ignore'] = @json(__('content.ignored_label_line_chart'));
        optionsDisabled['ignore'] = '';

        optionsHtml = '';
        for (var key in options) {
            optionsHtml+= '<option value="'+key+'" '+optionsDisabled[key]+'>'+options[key]+'</option>';
        }

        return optionsHtml;
    }

    function handleTabClick(clickedTabId) {
        const allTabs = ['overview', 'top-categories', 'top-words'];
        allTabs.forEach(tab => {
            const tabButton = document.getElementById(tab + '-tab');
            if (tab !== clickedTabId) {
                setElementStyle(tab + '-tab', 'color', '#6b6b6b');
                tabButton.setAttribute('aria-selected', 'false');
                tabButton.setAttribute('tabindex', '-1');
                document.getElementById(tab + '-line').classList.remove('analytics-tab-line');
                setElementStyle(tab, 'display', 'none');
                if (tab !== 'overview') {
                    setElementStyle(tab + '-content', 'visibility', 'hidden');
                } else {
                    setElementStyle(tab + '-wrapper', 'visibility', 'hidden');
                }
            } else {
                setElementStyle(tab + '-tab', 'color', '#6357b8');
                tabButton.setAttribute('aria-selected', 'true');
                tabButton.removeAttribute('tabindex');
                document.getElementById(tab + '-line').classList.add('analytics-tab-line');
                setElementStyle(tab + '-no-data', 'display', 'none');
                setElementStyle('loading-icon-' + tab, 'display', 'flex');
                setElementStyle('loading-icon-' + tab, 'visibility', 'visible');
                setParams();
                setElementStyle(tab, 'display', 'block');
            }
        });
    }

    document.addEventListener("DOMContentLoaded", function() {
        // Roving-tabindex arrow-key support for the chart tabs (WAI-ARIA tabs pattern).
        const tabButtons = Array.from(document.querySelectorAll('.analytics-tab-container [role="tab"]'));
        tabButtons.forEach(function(tab, i) {
            tab.addEventListener('keydown', function(event) {
                let next = null;
                if (event.key === 'ArrowRight') next = (i + 1) % tabButtons.length;
                if (event.key === 'ArrowLeft') next = (i - 1 + tabButtons.length) % tabButtons.length;
                if (next !== null) {
                    event.preventDefault();
                    tabButtons[next].focus();
                    tabButtons[next].click();
                }
            });
        });

        setCheckboxSelectLabels();
        const checkboxes = document.querySelector('.checkboxes');
        if (!checkboxes) return;
        let toggleNext = document.querySelectorAll('.toggle-next');

        function setCategoryPopupExpanded(expanded) {
            document.querySelectorAll('.toggle-next > button').forEach(function(button) {
                button.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            });
        }

        document.addEventListener('keydown', function(event) {
            // Only act while the category popup is actually open; a document-wide
            // Enter handler would otherwise hijack Enter for every control on the page.
            if ((event.key === 'Enter' || event.key === 'Escape') && checkboxes.style.display === 'block') {
                checkboxes.style.display = 'none';
                setCategoryPopupExpanded(false);
                setParams();
            }
        });

        document.addEventListener('click', function(e) {
            if (!e.target.classList.contains('checkboxes')
                && !e.target.classList.length == 0
                && !e.target.classList.contains('ckkBox')
                && !e.target.classList.contains('inner-wrap')
                && !e.target.classList.contains('checkbox-wrapper')
                && !e.target.classList.contains('ellipsis')
                && checkboxes.style.display === 'block'
                && !e.target.classList.contains('wittyworks-analytics-chart-extra-large')
            ) {
                checkboxes.style.display = 'none';
                setCategoryPopupExpanded(false);
                setParams();
            }
        });

        for (let i = 0; i < toggleNext.length; i++) {
            toggleNext[i].addEventListener('click', function(e) {
                if (checkboxes.style.display === 'none' || !checkboxes.style.display) {
                    checkboxes.style.display = 'block';
                    setCategoryPopupExpanded(true);
                }
            });
        }

        let ckkBoxes = document.querySelectorAll('.ckkBox');
        for (let j = 0; j < ckkBoxes.length; j++) {
            ckkBoxes[j].addEventListener('change', function() {
                if (this?.value === 'all_categories') {
                    const checkboxes = this.parentElement.parentElement.querySelectorAll('.ckkBox');
                    for (let m = 0; m < checkboxes.length; m++) {
                        checkboxes[m].checked = this.checked;
                    }
                }
                setCheckboxSelectLabels();
            });
        }
    });

    function getSelectedCategories () {
        const checkboxes = document.querySelectorAll('.ckkBox');
        const selectedCategories = [];
        for (let i = 0; i < checkboxes.length; i++) {
            if (checkboxes[i].checked) {
                selectedCategories.push(checkboxes[i]?.value);
            }
        }
        return selectedCategories;
    }

    function setCheckboxSelectLabels(elem) {
        const wrappers = document.querySelectorAll('.checkbox-wrapper');
        for (let k = 0; k < wrappers.length; k++) {
            const checkboxes = wrappers[k].querySelectorAll('.ckkBox');
            const label = wrappers[k].querySelector('.checkboxes').getAttribute('id');
            let prevText = '';
            for (let l = 0; l < checkboxes.length; l++) {
                const button = wrappers[k].querySelector('button');
                const numberOfChecked = wrappers[k].querySelectorAll('input.val[type="checkbox"]:checked').length;
                if (numberOfChecked === 6) {
                    button.textContent = @json(__('content.all_categories'));
                } else if (numberOfChecked === 0) {
                    button.textContent = @json(__('content.none_selected'));
                } else if (checkboxes[l].checked) {
                    const newText = checkboxes[l].nextElementSibling.innerHTML;
                    const btnText = prevText + newText.replace(/&amp;/g, '&');
                    button.textContent = btnText;
                    prevText = button.textContent + ', ';
                }
            }
       }
    }
</script>