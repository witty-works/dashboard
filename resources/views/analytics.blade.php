<?php
    $filter = isset($team) ? ['team_id' => $team->id] : ['user_id' => $user->id];
    $has_term_replacements = \App\Models\TermReplacement::where($filter)->exists();
    $dictionaryItems = isset($team)
        ? $team->getTotalTermReplacementsCount()
        : $user->getTotalTermReplacementsCount() + $user->currentTeam->getTotalTermReplacementsCount()
    ;
    $is_premium_user = isset($team) ? $team->subscribed() : $user->subscribed();
?>
<x-app-layout :pagetitle="__('content.analytics')">
<div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
<div class="wittyworks-page-wrapper" id="maincontent">
   <div class="wittyworks-page lg:ml-20">
      @include('partials.banners')
      <div class="ibarra-sub-title-h1 margin-top">
         {{ __('content.analytics') }}
      </div>
      <div id="lastRefresh" class="lato-small-text-p wittyworks-margin-right container-row margin-top" style="visibility: hidden; align-items: center;"></div>
      <div class="container-row margin-top">
        <div class="drowdown-wrapper">

            <div class="container-row">
                <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.chart_time_range') }}</div>
                @if (!$is_premium_user)
                <div style="margin-left: -1.5em"> @include('partials.locked')</div>
                @endif
            </div>
            @php
                $ranges = [
                    '1m' => __('content.chart_time_range_month'),
                    '3m' => __('content.chart_time_range_quarter'),
                    '1y' => __('content.chart_time_range_year'),
                ];
                if ($is_premium_user) {
                    $disabled = false;
                } else {
                    $disabled = [
                        '1m' => false,
                        '3m' => true,
                        '1y' => true,
                    ];
                }
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
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.language_filter') }}</div>

            @php
                $ranges = [
                    '' => __('content.language_filter_both'),
                    'en' => __('content.language_filter_en'),
                    'de' => __('content.language_filter_de'),
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
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.inclusive_filter') }}</div>
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
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.category_filter') }}</div>
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
            {{ __('content.analytic_overview_tab_title') }}
            <div class="analytics-tab-line" id="overview-line"></div>
        </div>
         <div class="analytics-tab" id="top-categories-tab" onclick="handleTabClick('top-categories')">
            {{ __('content.analytic_top_categories_tab_title') }}
            <div id="top-categories-line"></div>
        </div>
         <div class="analytics-tab" id="top-words-tab" onclick="handleTabClick('top-words')">
            {{ __('content.analytic_top_words_tab_title') }}
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
               <div class="container-row wittyworks-margin-right">
                  <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.start_of_week') }}</div>
                  <div id="startOfWeekDropdown"></div>
               </div>
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
                <div class="container-row wittyworks-margin-right">
                    <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.event_type') }}</div>
                    <div id="eventTypeDropdownTopCategories"></div>
                </div>
                  <div class="chart-container-row">
                     <canvas
                        id="topSubCategoriesChart"
                        class="wittyworks-analytics-chart-top-categories">
                     </canvas>
                     <div id="categoryOverview" class="wittyworks-margin-left"></div>
                     <div class="chart-footer" id="topSubCategoriesChartFooter"></div>
                  </div>
               </div>
            </div>
         </div>
      </div>

      <!-- Top words content here -->
      <div id="top-words" role="tabpanel" style="display: none;" aria-labelledby="top-words-tab">
         <div id="top-words-wrapper" style="width: 100%">
            <div class="wittyworks-form-section container border-radius mt-5">
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
                    <div class="container-row wittyworks-margin-right">
                        <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.event_type') }}</div>
                        <div id="eventTypeDropdownTopWords"></div>
                    </div>
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

<script>
    const setElementStyle = (elementId, property, value) => {
        const element = document.getElementById(elementId);
        if (element) {
            element.style[property] = value;
        }
    };

    function load_charts(refresh, startOfWeek, chartType = 'overview', timerange = '1m', interval = 'week', language = [], categories = [], inclusive = 'non_inclusive', eventTypes = null) {
        if (eventTypes === null) {
            const isPremiumUser = @json($is_premium_user);
            eventTypes = isPremiumUser ? ['check_result'] : ['popover_open']
        }

        if (refresh) {
            setElementStyle('lastRefresh', 'visibility', 'hidden');
            setElementStyle('loading-icon-overview', 'display', 'flex');
            setElementStyle('overview-wrapper', 'visibility', 'hidden');

            setElementStyle('loading-icon-top-words', 'display', 'flex');
            setElementStyle('top-words-wrapper', 'visibility', 'hidden');

            setElementStyle('loading-icon-top-catagories', 'display', 'flex');
            setElementStyle('top-categories-wrapper', 'visibility', 'hidden');
        }


        const colors = [
            "#241c5b",
            "#33277f",
            "#3a2c91",
            "#4132a4",
            "#4837b6",
            "#5240c5",
            "#6352ca",
            "#7365d0",
            "#8477d5",
            "#9489DB",
            "#a9a1e2",
            "#bfb8e9",
            "#cac4ed",
            "#d4d0f1",
            "#dfdcf4",
        ];

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
                formattedXValues[i] = '{{ __('content.week') }} ' + moment(xValues[i]).startOf('week').isoWeekday(startOfWeek).week() + ' ' + moment(xValues[i]).startOf('week').isoWeekday(startOfWeek).year();
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
        const textElement = getElement(sectionIdNoData + '-text');
        if (textElement) {
            if (isApiError) {
                textElement.innerHTML = '{!! __('content.no_data_image_text_temp_unavailable') !!}';
            } else {
                textElement.innerHTML = '{!! __('content.no_data_image_text') !!}';
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
            formattedSubcategories = '&' + subcategories.map(category => 'subcategories[]=' + subcategories).join('&');
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

    function createBarChart(chartId, xValues, yValues, text, display, singeColor, chart, from, interval, language, categories, subcategories = []) {
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
        document.getElementById(`${chartId}Footer`).innerText = footerText;

        getChartData(chart, newFrom, interval, language, categories, subcategories = [], to, inclusive, eventTypes).then(data => {
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
                                    const index = yValues.indexOf(currentValue);
                                    const currentLabel = xValues[index];
                                    const previousPeriodPopoverOpened = data.events.popover_open || {};
                                    if (previousPeriodPopoverOpened[currentLabel]) {
                                        const diff = currentValue - previousPeriodPopoverOpened[currentLabel];
                                        const percentage = (diff / previousPeriodPopoverOpened[currentLabel] * 100).toFixed(0);
                                        if (percentage == 0) return '+ 100 %';
                                        return percentage >= 0 ? '+' + percentage + '%' : percentage + '%';
                                    } else {
                                        return '+ 100 %';
                                    }
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
        });
    }

    chartType == 'overview' && getChartData('total', timerange, interval, language, categories, null,  null, inclusive, ['check_result','popover_open','alternative','ignore','learning_bites']).then(data => { //TODO
        if (data.errorStatus === 503) {
            handleNoData('loading-icon-overview', 'overview-no-data', '', true);
            return;
        }
        if (!data.events?.popover_open
            || Object.entries(data.events.popover_open).filter(([key, value]) => value > 0).length == 0) {
            handleNoData('loading-icon-overview', 'overview-no-data', '', false);
            return;
        }
        const events = data.events || {};
        const writing_streak = data.writing_streak || 0;
        const lastRefresh = data.last_refresh;

        let lastRefreshFormatted = moment(lastRefresh).fromNow();
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
            } else if (event === 'check_result') {
                for (const [date, count] of Object.entries(value)) {
                    xValuesCheckResult.push(date);
                    yValuesCheckResult.push(count);
                }
            }

            //insert drowdown with two options to id startOfWeekDropdown
            const startOfWeekDropdown = document.getElementById("startOfWeekDropdown").innerHTML = `<select id="startOfWeek" class="dropdown" onchange="setParams(this.value)">
                <option value="1">{{ __('content.monday') }}</option>
                <option value="6">{{ __('content.saturday') }}</option>
                <option value="7">{{ __('content.sunday') }}</option>
            </select>`;

            const weekdayOptions = document.getElementById("startOfWeek").options;
            //go through weekdayOptions and see if value is equal to startOfWeek
            for (let i = 0; i < weekdayOptions.length; i++) {
                if (weekdayOptions[i].value == startOfWeek) {
                    weekdayOptions[i].selected = true;
                }
            }

            //updateSection function
            function updateSection() {
                const lastRefreshDateFormatted = new Date(lastRefresh);
                const lastRefreshMinutes = Math.floor((new Date() - lastRefreshDateFormatted) / 60000);
                lastRefreshFormatted = moment(lastRefresh).fromNow();

                if (lastRefreshMinutes >= 3) {
                    return document.getElementById("lastRefresh").innerHTML = '{{ __('content.last_refreshed') }} &nbsp;' + lastRefreshFormatted + '&nbsp; &nbsp; <a class="button primary-button-red" onclick="load_charts(true, startOfWeek)">{{ __('content.refresh_data') }}</a>';
                }

                return document.getElementById("lastRefresh").innerHTML = '{{ __('content.last_refreshed') }} &nbsp;' + lastRefreshFormatted;
            };

            updateSection();
            setInterval(function() {
                updateSection()
            }, 30000);

            setElementStyle('llastRefresh', 'visibility', 'visible');

            const aggregatedCheckChatData =  formatChartLabels(xValuesPopoverOpen, yValuesCheck, interval);
            const aggregatedPopoverOpenData = formatChartLabels(xValuesPopoverOpen, yValuesPopoverOpen, interval)[1];
            const aggregatedAlternativeData = formatChartLabels(xValuesPopoverOpen, yValuesAlternative, interval)[1];
            const aggregatedIgnoreData = formatChartLabels(xValuesPopoverOpen, yValuesIgnore, interval)[1];
            const aggregatedLearningBitesData = formatChartLabels(xValuesPopoverOpen, yValuesLearningBites, interval)[1];
            const aggregatedCheckResultData = formatChartLabels(xValuesPopoverOpen, yValuesCheckResult, interval)[1];
            const isPremiumUser = @json($is_premium_user);

            if (aggregatedLearningBitesData.every(Number.isInteger)) {
                ctx = document.getElementById('eventsChart').getContext('2d');
                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: aggregatedCheckChatData[0],
                        datasets: [
                            {
                                data: aggregatedCheckResultData,
                                borderColor: colors[3],
                                fill: false,
                                label:  @json(__('content.check_result_label_line_chart')) + (!isPremiumUser ? ' ({{ __('teams.witty_teams_only') }})' : ''),
                                hidden: !isPremiumUser,
                            },
                            {
                                data: aggregatedPopoverOpenData,
                                borderColor: colors[6],
                                fill: false,
                                label: "{{ __('content.popover_label_line_chart') }}",
                                hidden: isPremiumUser,
                            },
                            {
                                data: aggregatedAlternativeData,
                                borderColor: colors[9],
                                fill: false,
                                label: "{{ __('content.alternative_label_line_chart') }}",
                                hidden: isPremiumUser,
                            },
                            {
                                data: aggregatedIgnoreData,
                                borderColor: colors[12],
                                fill: false,
                                label:  "{{ __('content.ignored_label_line_chart') }}",
                                hidden: isPremiumUser,
                            },
                            {
                                data: aggregatedLearningBitesData,
                                borderColor: colors[14],
                                fill: false,
                                label:  @json(__('content.learning_bites_label_line_chart')),
                                hidden: isPremiumUser,
                            },
                        ]
                    },
                    options: {
                        tooltips: {
                            mode: 'dataset',
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
                                        if (isPremiumUser) {
                                            isCurrentlyHidden = (i === 0 ? isCurrentlyHidden : !isCurrentlyHidden);
                                        } else {
                                            isCurrentlyHidden = (i === 0 ? true : false);
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
                        events: isPremiumUser ? ['click', 'mousemove', 'mouseout'] : [],
                        maintainAspectRatio: false,
                        responsive: true,
                        title: {
                        display: true,
                        text:  @json(__('content.title_line_chart')),
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
            }
        setElementStyle('loading-icon-overview', 'display', 'none');
        setElementStyle('overview-no-data', 'display', 'none');
        setElementStyle('overview-wrapper', 'visibility', 'visible');
        }
    });

    //ALWAYS ONLY past week
    chartType == 'top-categories' && getChartData('topSubcategories', '1w', interval, language, categories, null, null, inclusive, eventTypes).then(data => {
        if (!data.events ) {
            return;
        }
        const eventsPopoverOpenedWeekUnsorted = data.events.popover_open || {};
        const openedWeek = Object.entries(eventsPopoverOpenedWeekUnsorted).sort((a, b) => b[1] - a[1]).slice(0, 8);
        for (const [key, value] of openedWeek) {
            if (key && value) {
                xTopSubCategoriesWeek.push(key);
                yTopSubCategoriesWeek.push(value);
            }
        }
        setElementStyle('loading-icon-top-categories', 'display', 'none');
        setElementStyle('top-categories-content', 'visibility', 'visible');
    });


    (chartType == 'overview' || chartType == 'top-categories') && getChartData('topSubcategories', timerange, interval, language, categories, null,  null, inclusive, eventTypes).then(data => {
        if (data.errorStatus === 503) {
            handleNoData('loading-icon-top-categories', 'top-categories-no-data', 'topSubcategories', true);
            return;
        }
        if (!data.events || !data.subcategories ) {
            handleNoData('loading-icon-top-categories', 'top-categories-no-data', 'topSubcategories', false);
            return;
        }

        const isPremiumUser = @json($is_premium_user);

        const checkResultOptionDisabledAttr = isPremiumUser ? '' : 'disabled';
        const selectedDropdownValue = eventTypes[0] || (isPremiumUser ? 'check_result' : 'popover_open');

        const eventTypeDropdownTopWords = document.getElementById("eventTypeDropdownTopWords");
        eventTypeDropdownTopWords.innerHTML = `
                <select id="eventTypeTopWords" class="dropdown" onchange="setParams(1, [this.value])">
                    <option value="check_result" ${checkResultOptionDisabledAttr}>{{ __('content.check_result_label_line_chart') }}</option>
                    <option value="popover_open">{{ __('content.popover_label_line_chart') }}</option>
                    <option value="alternative">{{ __('content.alternative_label_line_chart') }}</option>
                    <option value="ignore">{{ __('content.ignored_label_line_chart') }}</option>
                </select>`;

        const eventTypeOptionsTopWords = document.getElementById("eventTypeTopWords").options;
        for (let i = 0; i < eventTypeOptionsTopWords.length; i++) {
            if (eventTypeOptionsTopWords[i].value == selectedDropdownValue) {
                eventTypeOptionsTopWords[i].selected = true;
            }
        }

        const eventTypeDropdownTopCategories = document.getElementById("eventTypeDropdownTopCategories");
        eventTypeDropdownTopCategories.innerHTML = `
                <select id="eventTypeTopCategories" class="dropdown" onchange="setParams(1, [this.value])">
                    <option value="check_result" ${checkResultOptionDisabledAttr}>{{ __('content.check_result_label_line_chart') }}</option>
                    <option value="popover_open">{{ __('content.popover_label_line_chart') }}</option>
                    <option value="alternative">{{ __('content.alternative_label_line_chart') }}</option>
                    <option value="ignore">{{ __('content.ignored_label_line_chart') }}</option>
                </select>`;

        const eventTypeOptionsTopCategories = document.getElementById("eventTypeTopCategories").options;
        for (let i = 0; i < eventTypeOptionsTopCategories.length; i++) {
            if (eventTypeOptionsTopCategories[i].value == selectedDropdownValue) {
                eventTypeOptionsTopCategories[i].selected = true;
            }
        }

        const subcategories = data.subcategories[selectedDropdownValue] || [];
        listOfLinks = ``;

        let locale = window.location.href.includes("/de/") ? "de" : "en"
        moment.locale(locale)

        let i = 0;
        for (const [key, value] of Object.entries(subcategories).slice(0, 15)) {
            let url = subcategories[key]['translation']['canonical_url'];
            let label = subcategories[key]['translation']['hs_name'];

            listOfLinks += `<div class="link-box" style="background-color:${colors[i]}"></div>`;

            if (url) {
                listOfLinks += `<a href="${url}" class="wittyworks-team-name lato-link-list" target="_blank">${label}</a>`;
            } else {
                listOfLinks += `<span class="lato-link-list">${label}</span>`;
            }

            listOfLinks += `</br>`;

            i++;
        }

        document.getElementById("categoryOverview").innerHTML = listOfLinks;

        const events = data.events[eventTypes[0]] || {};
        for (const [key, value] of Object.entries(events)) {
            xValuesTopSubCategories.push(key);
            yValuesTopSubCategories.push(value);
        }

        const xValuesTopSubCategoriesCut = xValuesTopSubCategories.slice(0, 15);
        const yValuesTopSubCategoriesCut = yValuesTopSubCategories.slice(0, 15);

        createBarChart(
            "topSubCategoriesChart",
            xValuesTopSubCategoriesCut,
            yValuesTopSubCategoriesCut,
            "{{ __('content.top_categories') }}",
            true,
            false,
            'topSubcategories',
            timerange,
            interval,
            language,
            categories
        );

        setElementStyle('loading-icon-top-categories', 'display', 'none');
        setElementStyle('top-categories-content', 'visibility', 'visible');
        setElementStyle('top-categories-content', 'display', 'block');
    });

    chartType == 'top-words' && getChartData('topWords', timerange, interval, language, categories, null, null, inclusive, eventTypes).then(data => {
        if (data.errorStatus === 503) {
            handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords', true);
            return;
        }

        if (!data.events ) {
            handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords', false);
            return;
        }

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

        createBarChart(
            "topWordsChart",
            xValuesTopWordsCut,
            yValuesTopWordsCut,
            "{{ __('content.title_words_bar_chart_month') }}",
            true,
            false,
            'topWords',
            timerange,
            interval,
            language,
            categories,
            ['corporate_rules']
        );
    });

    chartType == 'top-words' && getChartData('topWords', timerange, 'day', language, categories, ['corporate_rules'], null, inclusive, eventTypes).then(data => {
        if (data.errorStatus === 503) {
            handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords', true);
            return;
        }

        if (!data.events ) {
            handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords', false);
            return;
        }
        if (data && data.events) {
            const corporatewords = data.events[eventTypes[0]] || {};
            for (const [key, value] of Object.entries(corporatewords)) {
                if (!key || !value) continue;
                xValuesTopCorporateWords.push(key);
                yValuesTopCorporateWords.push(value);
            }

            if (xValuesTopCorporateWords.length === 0) {
                handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords');
                return;
            }

            const xValuesTopCorporateWordsCut = xValuesTopCorporateWords.slice(0, 15);
            const yValuesTopCorporateWordsCut = yValuesTopCorporateWords.slice(0, 15);
            createBarChart(
                "topWordsChartCorporateRules",
                xValuesTopCorporateWordsCut,
                yValuesTopCorporateWordsCut,
                "{{ __('content.title_words_bar_chart_month_corporate_rules') }}",
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

function setParams(startOfWeek = 1, eventTypes = null) {
    if (eventTypes === null) {
        const isPremiumUser = @json($is_premium_user);
        eventTypes = isPremiumUser ? ['check_result'] : ['popover_open']
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

    const selectedTimeRangeOption = timeRangeDropdown.options[timeRangeDropdown.selectedIndex].value;
    const selectedLanguageOption = languageDropdown.options[languageDropdown.selectedIndex].value;
    const selectedInclusiveOption = inclusiveDropdown.options[inclusiveDropdown.selectedIndex].value;

    const interval = selectedTimeRangeOption == '1y' ? 'month' : 'week';
    load_charts(false, startOfWeek, activeTab, selectedTimeRangeOption, interval, selectedLanguageOption, categories, selectedInclusiveOption, eventTypes);
}

load_charts(false, 1);

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
            if (this.value === 'all_categories') {
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
            selectedCategories.push(checkboxes[i].value);
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