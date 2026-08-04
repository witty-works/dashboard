<?php
    $filter = isset($team) ? ['team_id' => $team->id] : ['user_id' => $user->id];
    $has_term_replacements = \App\Models\TermReplacement::where($filter)->exists();
    $dictionaryItems = isset($team)
        ? $team->getTotalTermReplacementsCount()
        : $user->getTotalTermReplacementsCount() + $user->currentTeam->getTotalTermReplacementsCount()
    ;
?>
<x-app-layout :pagetitle="__('content.analytics')">
<nav class="wittyworks-navigation-wrapper" aria-label="Main Navigation">
    @livewire('navigation-menu')
</nav>
<div class="wittyworks-page-wrapper" id="maincontent">
   <div class="wittyworks-page lg:ml-20">
      @include('partials.banners')
      <h1 class="ibarra-sub-title-h1 margin-top">
         {{ __('content.analytics') }}
      </h1>
      <div id="lastRefresh" class="lato-small-text-p wittyworks-margin-right container-row margin-top" style="visibility: hidden; align-items: center;"></div>
      <div class="container-row margin-top">
        <div class="drowdown-wrapper">

            <div class="container-row">
                <div class="lato-small-text-p wittyworks-margin-right" aria-label="{{ __('content.chart_time_range') }}">{{ __('content.chart_time_range') }}</div>
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
                onchange="setParams()"
            />
        </div>

        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right" aria-label="{{ __('content.language_filter') }}">{{ __('content.language_filter') }}</div>

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
                onchange="setParams()"
            />
        </div>

        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right" aria-label="{{ __('content.inclusive_filter') }}">{{ __('content.inclusive_filter') }}</div>
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
                onchange="setParams()"
            />
        </div>


        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right" aria-label="{{ __('content.category_filter') }}">{{ __('content.category_filter') }}</div>
                <div class="toggle-next checkbox-wrapper">
                    <button class="ellipsis lato-small-text-p">{{ __('content.all_categories') }}</button>
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

      <div class="analytics-tab-container">
         <div class="analytics-tab" id="overview-tab" onclick="handleTabClick('overview')" style="color: #9489DB;">
            {{ __('content.analytic_overview') }}
            <div class="analytics-tab-line" id="overview-line"></div>
        </div>
         <div class="analytics-tab" id="top-categories-tab" onclick="handleTabClick('top-categories')">
            {{ __('content.top_categories') }}
            <div id="top-categories-line"></div>
        </div>
         <div class="analytics-tab" id="top-words-tab" onclick="handleTabClick('top-words')">
            {{ __('content.analytic_top_words') }}
            <div id="top-words-line"></div>
        </div>
      </div>

      <!-- Overview content here -->
      <div id="overview" role="tabpanel" aria-labelledby="overview-tab">
         <div class="wittyworks-form-section container border-radius mt-5">
            <div id="loading-icon-overview" class="loading-icon-wrapper" style="width: 100%">
               <div class="lds-grid">
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
                  <img src="{{ url('svg/screenshots/overviewNoData.png') }}" alt="activity" class="image">
                  <div id="overview-no-data-text" class="centered-image-text"></div>
               </div>
            </div>
            <div id="overview-wrapper" style="visibility: hidden; width: 100%">
               <div class="container-row wittyworks-margin-top">
                  <canvas id="eventsChart" class="wittyworks-analytics-chart-extra-large"></canvas>
               </div>
            </div>
         </div>
      </div>

      <!-- Top categories content here -->
      <div id="top-categories" role="tabpanel" style="display: none;" aria-labelledby="top-categories-tab">
         <div id="top-categories-wrapper" style="width: 100%">
            <div class="wittyworks-form-section container border-radius mt-5">
                <div class="container-row wittyworks-margin-right">
                    <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.event_type') }}</div>
                    <div id="eventTypeDropdownTopCategories"></div>
                </div>
                <div id="loading-icon-top-categories" class="loading-icon-wrapper"  style="width: 100%">
                    <div class="lds-grid">
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
                        <img src="{{ url('svg/screenshots/topCategoriesNoData.png') }}" alt="activity" class="image">
                        <div id="top-categories-no-data-text" class="centered-image-text"></div>
                    </div>
               </div>
               <div id="top-categories-content" style="visibility: hidden; width: 100%">
                    <div class="chart-container-row">
                        <canvas id="topSubCategoriesBarChart" class="wittyworks-analytics-chart-extra-large"></canvas>
                    </div>
                    <div class="chart-container-row">
                        <canvas id="topSubCategoriesChart" class="wittyworks-analytics-chart-extra-large"></canvas>
                        <button id="toggleLines" class="button primary-button-red">{{ __('content.toggle_lines') }}</button>
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
                    <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.event_type') }}</div>
                    <div id="eventTypeDropdownTopWords"></div>
                </div>
                <div id="loading-icon-top-words" class="loading-icon-wrapper" style="width: 100%">
                    <div class="lds-grid">
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
                        <img src="{{ url('svg/screenshots/topWordsNoData.png') }}" alt="activity" class="image">
                        <div id="top-words-no-data-text" class="centered-image-text"></div>
                    </div>
               </div>
               <div id="top-words-content" style="visibility: hidden; width: 100%">
                    <div class="chart-container-row">
                        <canvas
                            id="topWordsChart"
                            class="wittyworks-analytics-chart-extra-large wittyworks-margin-top">
                        </canvas>
                        <div class="chart-footer" id="topWordsChartFooter"></div>
                    </div>
                    <div class="chart-container-row wittyworks-margin-top">
                        <canvas
                            id="topWordsChartCorporateRules"
                            class="wittyworks-analytics-chart-extra-large">
                        </canvas>
                        <div class="chart-footer" id="topWordsChartCorporateRulesFooter"></div>
                    </div>
                  <div id="noCorporateRulesWrapper" class="container-row wittyworks-margin-top" style="display: none;">
                     <div class="lato-small-text-p">{!! empty($user) ? __('content.no_corporate_rules') : __('content.no_corporate_rules_user') !!}</div>
                  </div>
                  <div id="corporateRulesButNoneOpened" class="container-row wittyworks-margin-top" style="display: none;">
                     <div class="lato-small-text-p">{!! empty($user) ? __('content.corporate_rules_none_opened') : __('content.corporate_rules_none_opened_user') !!}</div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</x-app-layout>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/chartjs-plugin-datalabels/0.7.0/chartjs-plugin-datalabels.min.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/de.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/fr.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    moment.locale(@json(app()->getLocale()))
    let topSubChart;
    const setElementStyle = (elementId, property, value) => {
        const element = document.getElementById(elementId);
        if (element) {
            element.style[property] = value;
        }
    };

    function load_charts(refresh, chartType = 'overview', timerange = '1m', interval = 'week', language = [], categories = [], inclusive = 'non_inclusive', eventTypes = null) {
        if (eventTypes === null) {
            eventTypes = [@json($default_event)]
        }

        const colors = ['#f06567', '#f06773', '#f16980', '#f16b8c', '#f16d99', '#f16fa5', '#f172b1', '#f274be', '#f276ca', '#ed78d1',
 '#e57ad2', '#dc7bd3', '#d37dd4', '#cb7fd5', '#c280d6', '#b982d7', '#b184d8', '#a885d9', '#9f87da', '#9789db',
 '#8c8fdc', '#8197dd', '#76a0de', '#6ba9df', '#60b1e1', '#55bae2', '#4ac2e3', '#3fcbe4', '#38d1e4', '#3cd1de',
 '#41d0d9', '#45d0d4', '#49d0ce', '#4dd0c9', '#52cfc4', '#56cfbe', '#5acfb9'];

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

        Chart.defaults.global.defaultFontColor = '#000000';

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

                new Chart(ctx, {
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
                        title: {
                        display: true,
                        text: text,
                        fontSize: 16,
                        fontStyle: 'normal',
                        font: {
                            family: "Lato",
                        },
                        },
                        scales:{
                            xAxes: [{
                                display: display,
                                ticks: {
                                    //avoid very long labels
                                    callback: function(value, index, values) {
                                        if (value.length > 20) {
                                            return value.substring(0, 20) + '...';
                                        }
                                        return value;
                                    }
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    beginAtZero: true,
                                    min: 0,
                                    max: Math.max(...yValues) + Math.max(...yValues) * 0.15,
                                    callback: function(value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }],
                        },
                        plugins: {
                            datalabels: {
                                anchor: 'end',
                                align: 'top',
                                rotation: 315,
                                formatter: (currentValue) => {
                                    if (calledDirectly) return '';
                                    const index = yValues.indexOf(currentValue);
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
                        legend: {
                            display: display,
                        },
                        legend: {
                            display: false
                        },
                        tooltips: {
                            label: false
                        }
                    }
                });
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
                    const overviewChart = new Chart(ctx, {
                        type: "line",
                        data: {
                            labels: formatChartLabels(xValuesPopoverOpen, null, interval)[0],
                            datasets: datasetsOverview,
                        },
                        options: {
                            tooltips: {
                                enabled: true,
                                mode: 'nearest',
                                intersect: true,
                                backgroundColor: '#f5f5f5',
                                titleFontColor: 'black',
                                bodyFontColor: 'black',
                                callbacks: {
                                    title: function(tooltipItems, data) {
                                        return '';
                                    },
                                    label: function(tooltipItem, data) {
                                        return data.datasets[tooltipItem.datasetIndex].label + ': ' + tooltipItem.yLabel;
                                    },
                                    labelColor: function(tooltipItem, chart) {
                                        return {
                                            borderColor: 'rgba(0,0,0,0)',
                                            backgroundColor: 'rgba(0,0,0,0)'
                                        };
                                    },
                                    afterLabel: function(tooltipItem, data) {
                                        return '';
                                    }
                                },
                                displayColors: false
                            },
                            legend: {
                                onHover: function(e) {
                                    e.target.style.cursor = 'pointer';
                                },
                                onClick: function(e, legendItem) {
                                    const index = legendItem.datasetIndex;
                                    const ci = this.chart;
                                    const meta = ci.getDatasetMeta(index);
                                    meta.hidden = meta.hidden === null ? !ci.data.datasets[index].hidden : null;
                                    ci.update();
                                },
                                labels: {
                                    generateLabels: function(chart) {
                                        return chart.data.datasets.map((dataset, i) => {
                                            //workaround for charjs initial hidden state bug
                                            let isCurrentlyHidden = chart.getDatasetMeta(i).hidden === false || chart.getDatasetMeta(i).hidden === true;

                                            if (@json($default_event) !== 'popover_open') {
                                                if (showCheckHighlights) {
                                                    isCurrentlyHidden = (i === 0 ? isCurrentlyHidden : !isCurrentlyHidden);
                                                } else {
                                                    isCurrentlyHidden = (i === 0 ? true : false);
                                                }
                                            }
                                            return {
                                                datasetIndex: i,
                                                text: dataset.label,
                                                fillStyle: isCurrentlyHidden ? '#dddddd' : dataset.borderColor,
                                            };
                                        });
                                    },
                                    boxWidth: 12,
                                    fontSize: 12,
                                    usePointStyle: true,
                                },
                            },
                            hover: {
                                onHover: function(e) {
                                    var point = this.getElementAtEvent(e);
                                    if (point.length) e.target.style.cursor = 'pointer';
                                    else e.target.style.cursor = 'default';
                                }
                            },
                            events: ['click', 'mousemove', 'mouseout'],
                            maintainAspectRatio: false,
                            responsive: true,
                            title: {
                                display: true,
                                text:  @json(__('content.analytic_overview')),
                                fontSize: 16,
                                fontStyle: 'normal'
                            },
                            scales:{
                                yAxes: [{
                                    ticks: {
                                        min: 0,
                                        callback: function(value, index, values) {
                                            if (Math.floor(value) === value) {
                                                return value;
                                            }
                                        }
                                    }
                                }],
                            },
                            plugins: {
                                datalabels: {
                                    display: false,
                                }
                            },
                        }
                    });
                    window.addEventListener('resize', function() {
                        overviewChart.data.labels = formatChartLabels(xValuesPopoverOpen, null, interval)[0];
                    });
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
                <select id="eventTypeTopCategories" class="dropdown" onchange="setParams([this?.value])">
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
            topSubChart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: Object?.keys(data.events[eventTypes[0]][Object?.keys(data.events[eventTypes[0]])[0]].counts),
                    datasets: datasetsLineChart,
                    },
                    options: {
                        tooltips: {
                            enabled: true,
                            mode: 'nearest',
                            intersect: true,
                            backgroundColor: '#f5f5f5',
                            titleFontColor: 'black',
                            bodyFontColor: 'black',
                            callbacks: {
                                title: function(tooltipItems, data) {
                                    return '';
                                },
                                label: function(tooltipItem, data) {
                                    return data.datasets[tooltipItem.datasetIndex].label;
                                },
                                labelColor: function(tooltipItem, chart) {
                                    return {
                                        borderColor: 'rgba(0,0,0,0)',
                                        backgroundColor: 'rgba(0,0,0,0)'
                                    };
                                },
                                afterLabel: function(tooltipItem, data) {
                                    return '';
                                }
                            },
                            displayColors: false
                        },
                        onClick: function(e) {
                            const line = this.getElementAtEvent(e)[0];
                            if(!line) return;
                            const index = line._index;
                            const datasetIndex = line._datasetIndex;
                            const url = this.data.datasets[datasetIndex].url;
                            window.open(this.data.datasets[datasetIndex].url, '_blank');
                        },
                        legend: {
                            position: 'right',
                            onHover: function(e) {
                                e.target.style.cursor = 'pointer';
                            },
                            onClick: function(e, legendItem) {
                                const index = legendItem.datasetIndex;
                                topSubChart.data.datasets[index].hidden = !topSubChart.data.datasets[index].hidden;
                                topSubChart.update();
                            },
                            labels: {
                                usePointStyle: true,
                                fontSize: 14,
                                padding: 18,
                                generateLabels: function(chart) {
                                    return chart.data.datasets.map((dataset, i) => {
                                        let isHidden = dataset.hidden;
                                        return {
                                            datasetIndex: i,
                                            text: dataset.label,
                                            fillStyle: isHidden ? '#dddddd' : dataset.borderColor,
                                        };
                                    });
                                },
                            },
                        },
                        hover: {
                            onHover: function(e) {
                                var point = this.getElementAtEvent(e);
                                if (point.length) e.target.style.cursor = 'pointer';
                                else e.target.style.cursor = 'default';
                            }
                        },
                        events: ['click', 'mousemove', 'mouseout'],
                        maintainAspectRatio: false,
                        responsive: true,
                        title: {
                            display: true,
                            text:  @json(__('content.top_categories_over_time')),
                            fontSize: 16,
                            fontStyle: 'normal'
                        },
                        scales:{
                            xAxes: [{
                                ticks: {
                                    autoSkip: true,
                                    minRotation: 30
                                }
                            }],
                            yAxes: [{
                                ticks: {
                                    min: 0,
                                    callback: function(value, index, values) {
                                        if (Math.floor(value) === value) {
                                            return value;
                                        }
                                    }
                                }
                            }],
                        },
                        plugins: {
                            datalabels: {
                                display: false,
                            }
                        },
                    }
                });
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
                <select id="eventTypeTopWords" class="dropdown" onchange="setParams([this?.value])">
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
        let allVisible = topSubChart.data.datasets.every(dataset => !dataset.hidden);
        topSubChart.data.datasets.forEach(function(dataset) {
            dataset.hidden = allVisible;
        });

        topSubChart.update();
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

    load_charts(false);

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


        lastRefreshFormatted = moment(lastRefresh).fromNow();
        let innerHTML = '{{ __('content.last_refreshed') }} &nbsp;' + lastRefreshFormatted;

        const lastRefreshDateFormatted = new Date(lastRefresh);
        const lastRefreshMinutes = Math.floor((new Date() - lastRefreshDateFormatted) / 60000);
        if (lastRefreshMinutes >= 3) {
            innerHTML += '&nbsp; &nbsp; <a class="button primary-button-red" onclick="setParams(null, true); updateLastRefresh()">{{ __('content.refresh_data') }}</a>';
        }

        element.innerHTML = innerHTML
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
            if (tab !== clickedTabId) {
                setElementStyle(tab + '-tab', 'color', '#808080');
                document.getElementById(tab + '-line').classList.remove('analytics-tab-line');
                setElementStyle(tab, 'display', 'none');
                if (tab !== 'overview') {
                    setElementStyle(tab + '-content', 'visibility', 'hidden');
                } else {
                    setElementStyle(tab + '-wrapper', 'visibility', 'hidden');
                }
            } else {
                setElementStyle(tab + '-tab', 'color', '#9489DB');
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
        setCheckboxSelectLabels();
        const checkboxes = document.querySelector('.checkboxes');
        if (!checkboxes) return;
        let toggleNext = document.querySelectorAll('.toggle-next');

        document.addEventListener('keydown', function(event) {
            if ((event.key === 'Enter' || event.keyCode === 13) && checkboxes.style.display !== 'none') {
                checkboxes.style.display = 'none';
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
                && checkboxes.style.display !== 'none'
                && !e.target.classList.contains('wittyworks-analytics-chart-extra-large')
            ) {
                checkboxes.style.display = 'none';
                setParams();
            }
        });

        for (let i = 0; i < toggleNext.length; i++) {
            toggleNext[i].addEventListener('click', function(e) {
                if (checkboxes.style.display === 'none' || !checkboxes.style.display) {
                    checkboxes.style.display = 'block';
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