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
<div class="wittyworks-page-wrapper">
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
            <select class="dropdown margin-right" id="timerangeDropdown" onchange="setParams()">
                <option value="1m">{{ __('content.chart_time_range_month') }}</option>
                <option value="3m" {{ $is_premium_user ? '' : 'disabled' }}>{{ __('content.chart_time_range_quarter') }}</option>
                <option value="1y" {{ $is_premium_user ? '' : 'disabled' }}>{{ __('content.chart_time_range_year') }}</option>
            </select>
        </div>

        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.language_filter') }}</div>
            <select class="dropdown margin-right" id="languageDropdown" onchange="setParams()">
                <option value="">{{ __('content.language_filter_both') }}</option>
                <option value="en">{{ __('content.language_filter_en') }}</option>
                <option value="de">{{ __('content.language_filter_de') }}</option>
            </select>
        </div>


        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.category_filter') }}</div>
                <div class="checkbox-wrapper">
                    <button class="form-control toggle-next ellipsis lato-small-text-p">{{ __('content.all_categories') }}</button>
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
         <div class="ibarra-sub-title-h2 margin-top">{{ __('content.activity') }}</div>
         <div class="wittyworks-form-section container border-radius">
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
                  <img src="{{ url('svg/screenshots/activity.png') }}" alt="activity" class="image wittyworks-margin-top">
                  <div class="centered-image-text">{{ __('content.no_data_image_text') }}</div>
               </div>
            </div>
            <div id="overview-wrapper" style="visibility: hidden; width: 100%">
               <div class="wittyworks-margin-top">
                  <div class="container-column" style="margin-left: 2em;">
                     <div class="container-row" style="align-items: center">
                        <div id="checkDaysInRow" class="lato-small-text-p"></div>
                     </div>
                     <div class="container-row" >
                        <div id="changeInPopoverPercentage" class="lato-small-text-p"></div>
                     </div>
                     <div class="container-row" >
                        <div id="changeInAlternativePercentage" class="lato-small-text-p"></div>
                     </div>
                     <div class="container-row">
                        <div id="changeInIgnorePercentage" class="lato-small-text-p"></div>
                     </div>
                     <div class="container-row" >
                        <div id="changeInLearningBitesPercentage" class="lato-small-text-p"></div>
                     </div>
                  </div>
               </div>
               <div class="container-row wittyworks-margin-right wittyworks-margin-top" style="margin-left: 2em;">
                  <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.start_of_week') }}</div>
                  <div id="startOfWeekDropdown"></div>
               </div>
               <div class="container-row wittyworks-margin-top">
                  <canvas id="eventsChart" class="wittyworks-analytics-chart-extra-large"></canvas>
               </div>
               @if(isset($team) && !empty($team_edit))
               <div class="container-row wittyworks-margin-top">
                  <canvas id="dauChart" class="wittyworks-analytics-chart-extra-large"></canvas>
               </div>
               @endif
               <div class="container-row wittyworks-margin-top">
                  <canvas id="requestRatiosChartDoughnut" class="wittyworks-analytics-chart-small"></canvas>
                  @php
                  $eventsCharts = ["eventsPopoverChart", "eventsAlternativeChart", "eventsIgnoreChart", "eventsLearningBitesChart"];
                  for ($i = 0; $i < count($eventsCharts); $i++) {
                  echo '
                  <canvas id="' . $eventsCharts[$i] . '" class="wittyworks-analytics-chart-small wittyworks-margin-right"></canvas>
                  ';
                  }
                  @endphp
               </div>
            </div>
         </div>
      </div>
    
      <!-- Top categories content here -->
      <div id="top-categories" role="tabpanel" style="display: none;" aria-labelledby="top-categories-tab">
         <div id="top-categories-wrapper" style="width: 100%">
            <div class="ibarra-sub-title-h2 margin-top">{{ __('content.top_categories') }}</div>
            <div class="wittyworks-form-section container border-radius">
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
                        <img src="{{ url('svg/screenshots/activity.png') }}" alt="activity" class="image wittyworks-margin-top">
                        <div class="centered-image-text">{{ __('content.no_data_image_text') }}</div>
                    </div>
               </div>
               <div id="top-categories-content" style="visibility: hidden; width: 100%">
                  <div class="chart-container-row wittyworks-margin-top">
                     <canvas
                        id="topSubCategoriesChart"
                        class="wittyworks-analytics-chart-top-categories wittyworks-margin-top">
                     </canvas>
                     <div id="categoryOverview" class="wittyworks-margin-left wittyworks-margin-top"></div>
                  </div>
                  <div class="chart-container-row wittyworks-margin-top">
                     <canvas
                        id="categoriesRadar"
                        class="wittyworks-analytics-chart-medium-radar">
                     </canvas>
                     <div id="topSubcategoriesDoughnutWrapper" class="container-column">
                        <canvas
                           id="topSubCategoriesOpenedChartDoughnut"
                           class="wittyworks-analytics-chart-medium">
                        </canvas>
                        <canvas
                           id="topSubCategoriesIgnoredChartDoughnut"
                           class="wittyworks-analytics-chart-medium">
                        </canvas>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
     
      <!-- Top words content here -->
      <div id="top-words" role="tabpanel" style="display: none;" aria-labelledby="top-words-tab">
         <div id="top-words-wrapper" style="width: 100%">
            <div class="ibarra-sub-title-h2 margin-top">{{ __('content.top_words') }}</div>
            <div class="wittyworks-form-section container border-radius">
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
                        <img src="{{ url('svg/screenshots/activity.png') }}" alt="activity" class="image wittyworks-margin-top">
                        <div class="centered-image-text">{{ __('content.no_data_image_text') }}</div>
                    </div>
               </div>
               <div id="top-words-content" style="visibility: hidden; width: 100%">
                  <div id="topWordsChartCorporateRulesWrapper" class="chart-container-row wittyworks-margin-top" style="display: none; width: 100%; align-items:center">
                     <canvas
                        id="topWordsChartCorporateRules"
                        class="wittyworks-analytics-chart-extra-large wittyworks-margin-top">
                     </canvas>
                  </div>
                  <div id="noCorporateRulesWrapper" class="container-row wittyworks-margin-top" style="display: none;">
                     <div class="lato-small-text-p">{!! empty($user) ? __('content.no_corporate_rules') : __('content.no_corporate_rules_user') !!}</div>
                  </div>
                  <div id="corporateRulesButNoneOpened" class="container-row wittyworks-margin-top" style="display: none;">
                     <div class="lato-small-text-p">{!! empty($user) ? __('content.corporate_rules_none_opened') : __('content.corporate_rules_none_opened_user') !!}</div>
                  </div>
                  <div class="chart-container-row wittyworks-margin-top">
                     <canvas
                        id="wordsRadar"
                        class="wittyworks-analytics-chart-medium-radar">
                     </canvas>
                     <div id="topWordsDoughnutWrapper" class="container-column">
                        <canvas
                           id="topWordsChartDoughnut"
                           class="wittyworks-analytics-chart-medium">
                        </canvas>
                        <canvas
                           id="topWordsChartDoughnutWeek"
                           class="wittyworks-analytics-chart-medium">
                        </canvas>
                     </div>
                  </div>
               </div>
            </div>
         </div>
      </div>
   </div>
</div>
</x-app-layout>


<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/locale/de.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script>
    function load_charts(refresh, startOfWeek, chartType = 'overview', timerange = '1m', interval = 'week', language = [], categories = []) {
        if (refresh) {
            document.getElementById('lastRefresh').style.visibility = 'hidden';
            document.getElementById("loading-icon-overview").style.display = "flex";
            document.getElementById("overview-wrapper").style.visibility = "hidden";

            document.getElementById("loading-icon-top-words").style.display = "flex";
            document.getElementById("top-words-wrapper").style.visibility = "hidden";

            document.getElementById("loading-icon-top-catagories").style.display = "flex";
            document.getElementById("top-categories-wrapper").style.visibility = "hidden";
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

        const xIntervallDauWeek = [];
        const yValuesDauCheck = [];
        const yValuesDauIgnore = [];
        const yValuesDauAlternative = [];
        const yValuesDauPopoverOpen = [];
        const yValuesDauLearningBites = [];

        const xValuesCheck = [];
        const xValuesPopoverOpenIntervallWeek = [];
        const yValuesCheck = [];
        const yValuesCheckWeek = [];

        const xValuesPopoverOpen = [];
        const yValuesPopoverOpen = [];
        const yValuesPopoverOpenWeek = [];

        const xValuesIgnore = [];
        const xValuesIgnoreIntervallWeek = [];
        const yValuesIgnore = [];
        const yValuesIgnoreWeek = [];

        const xValuesAlternative = [];
        const yValuesAlternative = [];
        const yValuesAlternativeWeek = [];

        const xValuesLearningBites = [];
        const yValuesLearningBites = [];
        const yValuesLearningBitesWeek = [];

        const yValuesDauUserCount = [];

        const xTopSubCategoriesWeek = [];
        const yTopSubCategoriesWeek = [];
        const yTopSubCategoriesTwoWeeks = [];

        const xValuesTopSubCategoriesIgnored = [];
        const yValuesTopSubCategoriesIgnored = [];
        const xValuesTopSubCategoriesOpened = [];
        const yValuesTopSubCategoriesOpened = [];
        const xValuesTopSubCategoriesAlternative = [];
        const yValuesTopSubCategoriesAlternative = [];

        const xValuesTopWordsIgnored = [];
        const yValuesTopWordsIgnored = [];
        const xValuesTopWordsOpened = [];
        const yValuesTopWordsOpened = [];
        const xValuesTopWordsAlternative = [];
        const yValuesTopWordsAlternative = [];
        const xValuesTopCorporateWordsOpened = [];
        const yValuesTopCorporateWordsOpened = [];

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

    function handleNoData(loadingIconId, sectionIdNoData = null, chartId = null) {
        if (document.getElementById(loadingIconId)) {
            document.getElementById(loadingIconId).style.display = "none";
        }
       
        if (sectionIdNoData) {
            document.getElementById(sectionIdNoData).style.visibility = "visible";
            document.getElementById(sectionIdNoData).style.display = "block";
        } else if (chartId && document.getElementById(chartId)) {
            document.getElementById(chartId).style.display = "none";
        }
    }

    async function getChartData(chart, from = '1w', interval = 'day', categories, language) {
        let analyticsUrl = '/api/user/analytics?refresh=' + refresh + '&chart=';
        if (window.location.href.includes('team')) {
            analyticsUrl = '/api/team/analytics?refresh=' + refresh + '&chart=';
        }

        let formattedCategories = '';
        if (categories && categories.length) {
            formattedCategories = '&' + categories.map(category => 'categories[]=' + category).join('&');
        }

        analyticsUrl += chart + '&from=' + from + '&interval=' + interval + '&lang=' + language + formattedCategories;

        const response = await fetch(analyticsUrl, {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
                'X-App-Locale': '{{ app()->getLocale() }}',
            },
        });
         
        try {
            const data = await response.json();
            return data;
        } catch (e) {
            return null;
        }
    }

    function createBarChart(chartId, xValues, yValues, text, display, singeColor) {
        if (yValues.every((val, i, arr) => val === 0)) {
            document.getElementById(chartId).style.display = "none";
            return;
        }

        document.getElementById(chartId).style.display = 'flex';
        const ctx = document.getElementById(chartId).getContext('2d');

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
                            min: 0,
                            callback: function(value, index, values) {
                                if (Math.floor(value) === value) {
                                    return value;
                                }
                            }
                        }
                    }],
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

    function createDoughnutChart(chartId, xValues, yValues, text) {
        if (yValues.every((val, i, arr) => val === 0)) {
            document.getElementById(chartId).style.display = "none";
            return;
        }

        document.getElementById(chartId).style.display = 'flex';
        const ctx = document.getElementById(chartId).getContext('2d');
        new Chart(ctx, {
                type: "doughnut",
                data: {
                    labels: xValues,
                    datasets: [{
                    backgroundColor: [colors[6], colors[8], colors[10],colors[12], colors[14]],
                    data: yValues
                    }]
                },
                options: {
                    legend: {display: false},
                    maintainAspectRatio: false,
                    responsive: true,
                    tooltips: {
                        yAlign: 'bottom',
                        callbacks: {
                            labelColor: function(tooltipItem, chart) {
                                return {
                                    backgroundColor: chart.data.datasets[tooltipItem.datasetIndex].backgroundColor[tooltipItem.index],
                                    fontColor: "red",

                                }
                            },
                        },
                        backgroundColor: '#ffffff',
                        bodyFontColor: '#000000',
                    },
                    title: {
                        display: true,
                        text: text,
                        fontSize: 16,
                        fontStyle: 'normal',
                    }
                }
        });
    }

    chartType == 'overview' && getChartData('total', timerange, interval, categories, language).then(data => {
        if (!data?.events?.popover_open
            || Object.entries(data.events.popover_open).filter(([key, value]) => value > 0).length == 0
        ) {
            handleNoData('loading-icon-overview', 'overview-no-data');
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
                    // xValuesPopoverOpenIntervallWeek.push(moment(date).startOf('week').isoWeekday(startOfWeek).week());
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
            }
            //CURRENT WEEK
            const yValuesLearningBitesWeek = yValuesLearningBites.slice(-7);
            const xValuesLearningBitesWeek = xValuesLearningBites.slice(-7);
            const yValuesPopoverOpenWeek = yValuesPopoverOpen.slice(-7);
            const yValuesIgnoreWeek = yValuesIgnore.slice(-7);
            const yValuesAlternativeWeek = yValuesAlternative.slice(-7);

            //PREVIOUS WEEK
            const yValuesLearningBitesWeekPrevious = yValuesLearningBites.slice(-14, -7);
            const xValuesLearningBitesWeekPrevious = xValuesLearningBites.slice(-14, -7);
            const yValuesPopoverOpenWeekPrevious = yValuesPopoverOpen.slice(-14, -7);
            const yValuesIgnoreWeekPrevious = yValuesIgnore.slice(-14, -7);
            const yValuesAlternativeWeekPrevious = yValuesAlternative.slice(-14, -7);
            
            //TOTAL WEEK
            const totalWeeklyLearningBites = yValuesLearningBitesWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverOpen = yValuesPopoverOpenWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyIgnore = yValuesIgnoreWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyAlternative = yValuesAlternativeWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverClose = totalWeeklyPopoverOpen - totalWeeklyIgnore - totalWeeklyAlternative;
            const weeklyEvents = [totalWeeklyIgnore, totalWeeklyAlternative, totalWeeklyPopoverClose];
            
            //TOTAL WEEK PREVIOUS
            const totalWeeklyLearningBitesPrevious = yValuesLearningBitesWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverOpenPrevious = yValuesPopoverOpenWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyIgnorePrevious = yValuesIgnoreWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyAlternativePrevious = yValuesAlternativeWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const weeklyEventsPrevious = [totalWeeklyLearningBitesPrevious, totalWeeklyPopoverOpenPrevious, totalWeeklyIgnorePrevious, totalWeeklyAlternativePrevious];

            //WEEKLY CHANGE
            const changeInLearningBitesPercentage = (((totalWeeklyLearningBites - totalWeeklyLearningBitesPrevious) / (totalWeeklyLearningBitesPrevious == 0 ? 1 : totalWeeklyLearningBitesPrevious)) * 100).toFixed(0).replace('-', '');
            const changeInPopoverPercentage = (((totalWeeklyPopoverOpen - totalWeeklyPopoverOpenPrevious) / (totalWeeklyPopoverOpenPrevious == 0 ? 1 : totalWeeklyPopoverOpenPrevious)) * 100).toFixed(0).replace('-', '');
            const changeInIgnorePercentage = (((totalWeeklyIgnore - totalWeeklyIgnorePrevious) / (totalWeeklyIgnorePrevious == 0 ? 1 : totalWeeklyIgnorePrevious)) * 100).toFixed(0).replace('-', '');
            const changeInAlternativePercentage = (((totalWeeklyAlternative - totalWeeklyAlternativePrevious) / (totalWeeklyAlternativePrevious == 0 ? 1 : totalWeeklyAlternativePrevious)) * 100).toFixed(0).replace('-', '');
            
            document.getElementById("checkDaysInRow").innerHTML =  '{{ __('content.writing_streak') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' +  writing_streak + '&nbsp</span>' + '{{ __('content.in_a_row') }}';
            document.getElementById("changeInLearningBitesPercentage").innerHTML =  '{{ __('content.you_clicked') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInLearningBitesPercentage + '%</span>&nbsp;' + (changeInLearningBitesPercentage >= 0 ? '{{ __('content.learning_bites_requests_week_positive') }}' : '{{ __('content.learning_bites_requests_week_negative') }}');
            document.getElementById("changeInPopoverPercentage").innerHTML =  '{{ __('content.you_explored') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInPopoverPercentage + '%</span>&nbsp;' + (changeInPopoverPercentage >= 0 ? '{{ __('content.popover_open_week_positive') }}' : '{{ __('content.popover_open_week_negative') }}');
            document.getElementById("changeInAlternativePercentage").innerHTML =  '{{ __('content.you_selected') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInAlternativePercentage + '%</span>&nbsp;' + (changeInAlternativePercentage >= 0 ? '{{ __('content.alternative_clicked_week_positive') }}' : '{{ __('content.alternative_clicked_week_negative') }}');
            document.getElementById("changeInIgnorePercentage").innerHTML =  '{{ __('content.you_ignored') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInIgnorePercentage + '%</span>&nbsp;' + (changeInIgnorePercentage >= 0 ? '{{ __('content.ignored_words_week_positive') }}' : '{{ __('content.ignored_words_week_negative') }}');

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
            document.getElementById('lastRefresh').style.visibility = 'visible';

            const aggregatedCheckChatData =  formatChartLabels(xValuesPopoverOpen, yValuesCheck, interval);
            const aggregatedPopoverOpenData = formatChartLabels(xValuesPopoverOpen, yValuesPopoverOpen, interval)[1];
            const aggregatedAlternativeData = formatChartLabels(xValuesPopoverOpen, yValuesAlternative, interval)[1];
            const aggregatedIgnoreData = formatChartLabels(xValuesPopoverOpen, yValuesIgnore, interval)[1];
            const aggregatedLearningBitesData = formatChartLabels(xValuesPopoverOpen, yValuesLearningBites, interval)[1];

            if(aggregatedLearningBitesData.every(Number.isInteger)) {
                ctx = document.getElementById('eventsChart').getContext('2d');
                new Chart(ctx, {
                    type: "line",
                    data: {
                        labels: aggregatedCheckChatData[0],
                        datasets: [
                            {
                                data: aggregatedPopoverOpenData,
                                borderColor: colors[3],
                                fill: false,
                                label: "{{ __('content.popover_label_line_chart') }}",
                            },
                            {
                                data: aggregatedAlternativeData,
                                borderColor: colors[6],
                                fill: false,
                                label: "{{ __('content.alternative_label_line_chart') }}",
                            },
                            {
                                data: aggregatedIgnoreData,
                                borderColor: colors[9],
                                fill: false,
                                label:  "{{ __('content.ignored_label_line_chart') }}",
                            },
                            {
                                data: aggregatedLearningBitesData,
                                borderColor: colors[12],
                                fill: false,
                                label:  @json(__('content.learning_bites_label_line_chart')),
                            },
                        ]
                    },
                    options: {
                        events: [],
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
                        }
                    }
                });
            }

            createBarChart(
                "eventsLearningBitesChart",
                xValuesLearningBitesWeek,
                yValuesLearningBitesWeek,
                @json(__('content.title_bar_chart_learning_bites')),
                false,
                true
            );

            createBarChart(
                "eventsPopoverChart",
                xValuesLearningBitesWeek,
                yValuesPopoverOpenWeek,
                "{{ __('content.title_bar_chart_popover') }}",
                false,
                true
            );

            createBarChart(
                "eventsIgnoreChart",
                xValuesLearningBitesWeek,
                yValuesIgnoreWeek,
                "{{ __('content.title_bar_chart_ignored') }}",
                false,
                true
            );

            createBarChart(
                "eventsAlternativeChart",
                xValuesLearningBitesWeek,
                yValuesAlternativeWeek,
                "{{ __('content.title_bar_chart_alternative') }}",
                false,
                true
            );

                createDoughnutChart(
                "requestRatiosChartDoughnut",
                [
                "{{ __('content.ignored_doughnut_chart_event_ratio') }}",
                "{{ __('content.alternative_doughnut_chart_event_ratio') }}",
                "{{ __('content.popover_closed_doughnut_chart_event_ratio') }}"
                ],
                weeklyEvents,
                "{{ __('content.title_doughnut_chart_event_ratio') }}",
            );
           
        getChartData('dau', timerange, 'week', categories, language).then(data => {
        if (!data?.events?.popover_open
            || Object.entries(data.events.popover_open).filter(([key, value]) => value > 0).length == 0
        ) {
            return;
        }
        const events = data.events || {};

        for (const [event, value] of Object.entries(events)) {
            if (event === 'popover_open') {
                for (const [date, count] of Object.entries(value)) {
                    xIntervallDauWeek.push(date);
                    yValuesDauPopoverOpen.push(count);
                }
            } else if (event === 'ignore') {
                for (const [date, count] of Object.entries(value)) {
                    yValuesDauIgnore.push(count);
                }
            } else if (event === 'alternative') {
                for (const [date, count] of Object.entries(value)) {
                    yValuesDauAlternative.push(count);
                }
            } else if (event === 'learning_bites') {
                for (const [date, count] of Object.entries(value)) {
                    yValuesDauLearningBites.push(count);
                }
            } else if (event === 'user_count') {
                for (const [date, count] of Object.entries(value)) {
                    yValuesDauUserCount.push(count);
                }
            }
        }
        if(document.getElementById('dauChart')) {

            ctx = document.getElementById('dauChart').getContext('2d');
            new Chart(ctx, {
                type: "line",
                data: {
                    labels: aggregatedCheckChatData[0],  //until posthog offers select start of week
                    datasets: [
                        {
                            data: yValuesDauUserCount,
                            borderColor: colors[2],
                            fill: false,
                            label: "{{ __('content.user_count_label_line_chart_dau') }}",
                        },
                        {
                            data: yValuesDauPopoverOpen,
                            borderColor: colors[6],
                            fill: false,
                            label: "{{ __('content.popover_label_line_chart_dau') }}",
                        },
                        {
                            data: yValuesDauAlternative,
                            borderColor: colors[8],
                            fill: false,
                            label: "{{ __('content.alternative_label_line_chart_dau') }}",
                        },
                        {
                            data: yValuesDauIgnore,
                            borderColor: colors[10],
                            fill: false,
                            label:  "{{ __('content.ignored_label_line_chart_dau') }}",
                        },
                        {
                            data: yValuesDauLearningBites,
                            borderColor: colors[12],
                            fill: false,
                            label: @json(__('content.learning_bites_label_line_chart_dau')),
                        },
                    ]
                },
                options: {
                    events: [],
                    maintainAspectRatio: false,
                    responsive: true,
                    animation: {
                        duration: 0
                    },
                    title: {
                    display: true,
                    text: "{{ __('content.title_line_chart_dau') }}",
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
                    }
                }
            });
        }
            document.getElementById("loading-icon-overview").style.display = "none";
            document.getElementById("overview-no-data").style.display = "none";
            document.getElementById("overview-wrapper").style.visibility = "visible";
        });
        }
    });

    //ALWAYS ONLY past week
    chartType == 'top-categories' && getChartData('topSubcategories', '1w', interval, categories, language).then(data => {
        if (!data || !data.events ) {
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

        getChartData('topSubcategories', '2w', interval, categories, language).then(data => {
        if (!data || !data.events ) {
            return;
        }
        const openedTwoWeeks = data.events.popover_open || {};
        for (const [key, value] of Object.entries(openedTwoWeeks)) {
            if (!xTopSubCategoriesWeek.includes(key)) {
                delete openedTwoWeeks[key];
            }
        }

        for (const [key, value] of openedWeek) {
            if (key && value && openedTwoWeeks[key]) {
                yTopSubCategoriesTwoWeeks.push(openedTwoWeeks[key] - value);
            } else {
                yTopSubCategoriesTwoWeeks.push(0);
            }
        }

        if (yTopSubCategoriesTwoWeeks.every((val, i, arr) => val === arr[0])
            || yTopSubCategoriesWeek.every((val, i, arr) => val === arr[0])
        ) {
            document.getElementById("categoriesRadar").style.display = "none";
            document.getElementById("topSubcategoriesDoughnutWrapper").classList.remove("container-column");
            document.getElementById("topSubcategoriesDoughnutWrapper").classList.add("container-row");
            document.getElementById("topSubcategoriesDoughnutWrapper").style.width = "100%";
            document.getElementById("topSubcategoriesDoughnutWrapper").style.justifyContent = "space-around";
            return;
        }

        const dataCategories = {
                labels: xTopSubCategoriesWeek,
                datasets: [{
                    label: "{{ __('content.title_categories_radar_chart_last_week') }}",
                    data: yTopSubCategoriesTwoWeeks,
                    fill: true,
                    backgroundColor: 'hsla(247, 52.8%, 75.9%, 0.5)',
                    borderColor: 'hsla(248, 53.2%, 60.6%, 0.5)'
                }, {
                    label: "{{ __('content.title_categories_radar_chart_current_week') }}",
                    data: yTopSubCategoriesWeek,
                    fill: true,
                    backgroundColor: 'hsla(247, 54.1%, 88.0%, 0.5)',
                    borderColor: 'hsla(247, 52.8%, 75.9%, 0.5)'
                }]
                };
  
            ctx = document.getElementById('categoriesRadar').getContext('2d');
            new Chart(ctx, {
                type: 'radar',
                    data: dataCategories,
                    options: {
                        maintainAspectRatio: false,
                        responsive: true,
                        events: [],
                        elements: {
                        line: {
                            borderWidth: 1
                        },
                        opacity: 0.5,
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
                        }
                    }
                },
            });
        });
        document.getElementById("loading-icon-top-categories").style.display = "none";
        document.getElementById("top-categories-content").style.visibility = "visible";
    });


    (chartType == 'overview' || chartType == 'top-categories') && getChartData('topSubcategories', timerange, interval, categories, language).then(data => {
        if (!data || !data.events || !data.subcategories ) {
            handleNoData('loading-icon-top-categories', 'top-categories-no-data', 'topSubcategories');
            return;
        }

        const subcategories = data.subcategories.popover_open || {};
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

        const ignored = data.events.ignore || {};
        const opened = data.events.popover_open || {};
        const alternative = data.events.alternative || {};

        for (const [key, value] of Object.entries(ignored)) {
            xValuesTopSubCategoriesIgnored.push(key);
            yValuesTopSubCategoriesIgnored.push(value);
        }

        for (const [key, value] of Object.entries(opened)) {
            xValuesTopSubCategoriesOpened.push(key);
            yValuesTopSubCategoriesOpened.push(value);
        }

        for (const [key, value] of Object.entries(alternative)) {
            xValuesTopSubCategoriesAlternative.push(key);
            yValuesTopSubCategoriesAlternative.push(value);
        }

        const xValuesTopSubCategoriesIgnoredCutDoughnut = xValuesTopSubCategoriesIgnored.slice(0, 5);
        const yValuesTopSubCategoriesIgnoredCutDoughnut = yValuesTopSubCategoriesIgnored.slice(0, 5);

        const xValuesTopSubCategoriesOpenedCut = xValuesTopSubCategoriesOpened.slice(0, 15);
        const yValuesTopSubCategoriesOpenedCut = yValuesTopSubCategoriesOpened.slice(0, 15);

        const xValuesTopSubCategoriesAlternativeCutDoughnut = xValuesTopSubCategoriesAlternative.slice(0, 5);
        const yValuesTopSubCategoriesAlternativeCutDoughnut = yValuesTopSubCategoriesAlternative.slice(0, 5);
    

        createBarChart(
            "topSubCategoriesChart",
            xValuesTopSubCategoriesOpenedCut,
            yValuesTopSubCategoriesOpenedCut,
            "{{ __('content.title_categories_bar_chart_month') }}",
            true,
            false
        );

        createDoughnutChart(
            "topSubCategoriesOpenedChartDoughnut",
            xValuesTopSubCategoriesAlternativeCutDoughnut,
            yValuesTopSubCategoriesAlternativeCutDoughnut,
            "{{ __('content.title_categories_alternative_doughnut_chart_month') }}",

        );
    
        createDoughnutChart(
            "topSubCategoriesIgnoredChartDoughnut",
            xValuesTopSubCategoriesIgnoredCutDoughnut,
            yValuesTopSubCategoriesIgnoredCutDoughnut,
            "{{ __('content.title_categories_ignored_doughnut_chart_month') }}",
        );

        document.getElementById("loading-icon-top-categories").style.display = "none";
        document.getElementById("top-categories-content").style.visibility = "visible";
        document.getElementById("top-categories-content").style.display = "block";
    });

    //ALWAYS ONLY past week
    chartType == 'top-words' && getChartData('topWords', '1w', interval, categories, language).then(data => {
        if (!data || !data.events ) {
            return;
        }
        const xTopWordsWeek = [];
        const yTopWordsWeek = [];
        const yTopWordsTwoWeeks = [];
        const popoverOpenedWeekUnsorted = data.events.popover_open || {};
        const openedWeek = Object.entries(popoverOpenedWeekUnsorted).sort((a, b) => b[1] - a[1]).slice(0, 8);
        for (const [key, value] of openedWeek) {
            if (key && value) {
                xTopWordsWeek.push(key);
                yTopWordsWeek.push(value);
            }
        }

        getChartData('topWords', '2w', interval, categories, language).then(data => {
        if (!data || !data.events ) {
            return;
        }
        const openedTwoWeeks = data.events.popover_open || {};
        for (const [key, value] of Object.entries(openedTwoWeeks)) {
            if (!xTopWordsWeek.includes(key)) {
                delete openedTwoWeeks[key];
            }
        }
        for (const [key, value] of openedWeek) {
            if (key && value && openedTwoWeeks[key]) {
                yTopWordsTwoWeeks.push(openedTwoWeeks[key] - value);
            } else if (key && value) {
                yTopWordsTwoWeeks.push(0);
            }
        }

        if (yTopWordsTwoWeeks.every((val, i, arr) => val === arr[0]) || yTopWordsWeek.every((val, i, arr) => val === arr[0])) {
            document.getElementById("wordsRadar").style.display = "none";
            document.getElementById("topWordsDoughnutWrapper").classList.remove("container-column");
            document.getElementById("topWordsDoughnutWrapper").classList.add("container-row");
            document.getElementById("topWordsDoughnutWrapper").style.width = "100%";
            document.getElementById("topWordsDoughnutWrapper").style.justifyContent = "space-around";
            return;
        }

        const dataCategories = {
            labels:
            xTopWordsWeek,
            datasets: [{
                label: "{{ __('content.title_words_radar_chart_last_week') }}",
                data: yTopWordsTwoWeeks,
                fill: true,
                backgroundColor: 'hsla(247, 52.8%, 75.9%, 0.5)',
                    borderColor: 'hsla(248, 53.2%, 60.6%, 0.5)'
            }, {
                label: "{{ __('content.title_words_radar_chart_current_week') }}",
                data: yTopWordsWeek,
                fill: true,
                backgroundColor: 'hsla(247, 54.1%, 88.0%, 0.5)',
                    borderColor: 'hsla(247, 52.8%, 75.9%, 0.5)'
    
            }]
        };

        ctx = document.getElementById('wordsRadar').getContext('2d');
        new Chart(ctx, {
            type: 'radar',
            data: dataCategories,
            options: {
                events: [],
                maintainAspectRatio: false,
                responsive: true,
                elements: {
                    line: {
                        borderWidth: 1
                        }
                    }
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
                }
            });
           
        });
    });

    chartType == 'top-words' && getChartData('topWords', timerange, interval, categories, language).then(data => {
        if (!data || !data.events ) {
            handleNoData('loading-icon-top-words', 'top-words-no-data', 'topWords');
            return;
        }
        const ignored = data.events.ignore || {};
        const opened = data.events.popover_open || {};
        const alternative = data.events.alternative || {};

        for (const [key, value] of Object.entries(ignored)) {
            xValuesTopWordsIgnored.push(key);
            yValuesTopWordsIgnored.push(value);
        }

        for (const [key, value] of Object.entries(opened)) {
            xValuesTopWordsOpened.push(key);
            yValuesTopWordsOpened.push(value);
        }

        for (const [key, value] of Object.entries(alternative)) {
            xValuesTopWordsAlternative.push(key);
            yValuesTopWordsAlternative.push(value);
        }

        const xValuesTopWordsIgnoredCutDoughnut = xValuesTopWordsIgnored.slice(0, 5);
        const yValuesTopWordsIgnoredCutDoughnut = yValuesTopWordsIgnored.slice(0, 5);

        const xValuesTopWordsOpenedCut = xValuesTopWordsOpened.slice(0, 15);
        const yValuesTopWordsOpenedCut = yValuesTopWordsOpened.slice(0, 15);
     
        const xValuesTopWordsAlternativeCutDoughnut = xValuesTopWordsAlternative.slice(0, 5);
        const yValuesTopWordsAlternativeCutDoughnut = yValuesTopWordsAlternative.slice(0, 5);

        createDoughnutChart(
            "topWordsChartDoughnut",
            xValuesTopWordsAlternativeCutDoughnut,
            yValuesTopWordsAlternativeCutDoughnut,
            "{{ __('content.title_words_alternative_doughnut_chart_month') }}",
        );

        createDoughnutChart(
            "topWordsChartDoughnutWeek",
            xValuesTopWordsIgnoredCutDoughnut,
            yValuesTopWordsIgnoredCutDoughnut,
            "{{ __('content.title_words_ignored_doughnut_chart_month') }}",
        );

        document.getElementById("loading-icon-top-words").style.display = "none";
        document.getElementById("top-words-content").style.visibility = "visible";
        document.getElementById("top-words-content").style.display = "block";
    });

    chartType == 'top-words' && getChartData('topWords', timerange, 'day', categories, language).then(data => {
        if(data && data.events && data.events.popover_open) {
            const openedCorporatewords = data.events.popover_open;
            for (const [key, value] of Object.entries(openedCorporatewords)) {
                xValuesTopCorporateWordsOpened.push(key);
                yValuesTopCorporateWordsOpened.push(value);
            }
            if(xValuesTopCorporateWordsOpened.length >= 10) {
                xValuesTopCorporateWordsOpened.slice(0, 10);
                yValuesTopCorporateWordsOpened.slice(0, 10);
            } else if (xValuesTopCorporateWordsOpened.length > 0) {
                for (let i = xValuesTopCorporateWordsOpened.length; i < 10; i++) {
                    xValuesTopCorporateWordsOpened.push("");
                    yValuesTopCorporateWordsOpened.push(0);
                }
            }

            createBarChart(
                "topWordsChartCorporateRules",
                xValuesTopCorporateWordsOpened,
                yValuesTopCorporateWordsOpened,
                "{{ __('content.title_words_bar_chart_month_corporate_rules') }}",
                true,
                false
            );

            document.getElementById("topWordsChartCorporateRulesWrapper").style.display = "flex";
        } else {
            if ({{ $dictionaryItems}}) {
                document.getElementById("corporateRulesButNoneOpened").style.display = "flex";
            } else {
                document.getElementById("noCorporateRulesWrapper").style.display = "flex";
            }
        }
    });
};

function setParams(startOfWeek = 1) {
    const activeTab = document.getElementsByClassName("analytics-tab-line")[0].id.replace('-line', '');
    if (activeTab) {
        document.getElementById('loading-icon-' + activeTab).style.display = 'flex';
        document.getElementById('loading-icon-' + activeTab).style.visibility = 'visible';
        document.getElementById(activeTab + '-no-data').style.display = 'none';

        if (activeTab === 'overview' && document.getElementById(activeTab + '-wrapper')){
            document.getElementById(activeTab + '-wrapper').style.visibility = "hidden";
        } else if (document.getElementById(activeTab + '-content')) {
            document.getElementById(activeTab + '-content').style.visibility = "hidden";
        }
    }

    const categories = getSelectedCategories();
    const timeRangeDropdown = document.getElementById("timerangeDropdown");
    const languageDropdown = document.getElementById("languageDropdown");

    const selectedTimeRangeOption = timeRangeDropdown.options[timeRangeDropdown.selectedIndex].value;
    const selectedLanguageOption = languageDropdown.options[languageDropdown.selectedIndex].value;

    const interval = selectedTimeRangeOption == '1y' ? 'month' : 'week';
    load_charts(false, startOfWeek, activeTab, selectedTimeRangeOption, interval, selectedLanguageOption, categories);
}

load_charts(false, 1);

function handleTabClick(clickedTabId) {
    const allTabs = ['overview', 'top-categories', 'top-words'];
    allTabs.forEach(tab => {
        if (tab !== clickedTabId) {
            document.getElementById(tab + '-tab').style.color = '#808080';
            document.getElementById(tab + '-line').classList.remove('analytics-tab-line');
            document.getElementById(tab).style.display = 'none';
            if (tab !== 'overview') {
                document.getElementById(tab + '-content').style.visibility = "hidden";
            } else {
                document.getElementById(tab + '-wrapper').style.visibility = "hidden";
            }
        } else {
            document.getElementById(tab + '-tab').style.color = '#9489DB';
            document.getElementById(tab + '-line').classList.add('analytics-tab-line');
            document.getElementById(tab + '-no-data').style.display = 'none';
            document.getElementById('loading-icon-' + tab).style.display = 'flex';
            document.getElementById('loading-icon-' + tab).style.visibility = 'visible';
            setParams();
            document.getElementById(tab).style.display = 'block';
        }
    });
}


document.addEventListener("DOMContentLoaded", function() {
  setCheckboxSelectLabels();
  let toggleNext = document.querySelectorAll('.toggle-next');
  document.addEventListener('click', function(e) {
    const checkboxes = document.querySelector('.checkboxes');
        if (!e.target.classList.contains('checkboxes')
            && !e.target.classList.contains('toggle-next')
            && !e.target.classList.length == 0
            && !e.target.classList.contains('ckkBox')
            && !e.target.classList.contains('inner-wrap')
            && !e.target.classList.contains('checkbox-wrapper')
            && checkboxes.style.display !== 'none'
        ) {
            checkboxes.style.display = 'none';
            setParams()
        }
    });
  for (let i = 0; i < toggleNext.length; i++) {
    toggleNext[i].addEventListener('click', function() {
      const checkboxes = this.nextElementSibling;
      if (checkboxes.style.display === 'none') {
        checkboxes.style.display = 'block';
      } else {
        checkboxes.style.display = 'none';
        setParams()
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
            button.textContent = 'All categories';
        } else if (numberOfChecked === 0) {
            button.textContent = 'None selected';
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