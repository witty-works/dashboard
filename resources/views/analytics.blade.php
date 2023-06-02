<?php 
    $filter = isset($team) ? ['team_id' => $team->id] : ['user_id' => $user->id];
    $has_term_replacements = \App\Models\TermReplacement::where($filter)->exists();
    $dictionaryItems = isset($team) ? $team->getTotalTermReplacementsCount() : $user->getTotalTermReplacementsCount() + $user->currentTeam->getTotalTermReplacementsCount();

$categoriesWithSubcategories = [
    'category_filter_cultural_diversity' => [
        'category_filter_cultural_diversity_racism',
        'category_filter_cultural_diversity_xenophobia',
        'category_filter_cultural_diversity_cultural_stereotype',
        'category_filter_cultural_diversity_migration_background',
        'category_filter_cultural_diversity_nazi_language',
        'category_filter_cultural_diversity_yiddish_pejoratives',
        'category_filter_cultural_diversity_ancestry',
        'category_filter_cultural_diversity_color',
        'category_filter_cultural_diversity_abbreviation',
        'category_filter_cultural_diversity_clarity',
        'category_filter_cultural_diversity_empty_words',
        'category_filter_cultural_diversity_filler_words',
        'category_filter_cultural_diversity_plain_english'
    ],
    'category_filter_gender_orientation' => [
        'category_filter_gender_orientation_homophobia',
        'category_filter_gender_orientation_sexism',
        'category_filter_gender_orientation_transphobia',
        'category_filter_gender_orientation_binary_pronouns',
        'category_filter_gender_orientation_female_stereotype',
        'category_filter_gender_orientation_gender_binary',
        'category_filter_gender_orientation_gender_cues',
        'category_filter_gender_orientation_gender_specific_abbreviation',
        'category_filter_gender_orientation_generic_masculine',
        'category_filter_gender_orientation_generic_plural',
        'category_filter_gender_orientation_jobs',
        'category_filter_gender_orientation_male_stereotype',
        'category_filter_gender_orientation_male_stereotype',
        'category_filter_gender_orientation_sexual_orientation',
        'category_filter_gender_orientation_traditional_leadership',
    ],
    'category_filter_ability_physicality' => [
        'category_filter_ability_physicality_ableist',
        'category_filter_ability_physicality_ability',
        'category_filter_ability_physicality_physicality',
        'category_filter_ability_physicality_behavior',
        'category_filter_ability_physicality_medical_state',
        'category_filter_ability_physicality_mobility',
        'category_filter_ability_physicality_cognitive_ability',
        'category_filter_ability_physicality_cognitive_perception',
        'category_filter_ability_physicality_hearing',
        'category_filter_ability_physicality_learning_ability',
        'category_filter_ability_physicality_mental_wellbeing',
        'category_filter_ability_physicality_speech_ability',
        'category_filter_ability_physicality_vision',
    ],
    'category_filter_religion' => [
        'category_filter_religion_anti_semitism',
        'category_filter_religion_islamophobia_anti_muslim_sentiment',
        'category_filter_religion_belief',
    ],
    'category_filter_acquired_diversity' => [
        'category_filter_acquired_diversity_classism',
        'category_filter_acquired_diversity_formality',
        'category_filter_acquired_diversity_25_years',
        'category_filter_acquired_diversity_50_years',
        'category_filter_acquired_diversity_age_information',
    ],
    'category_filter_social_motive' => [
        'category_filter_social_motive_offensive_language',
        'category_filter_social_motive_agentic_language',
        'category_filter_social_motive_exaggeration',
        'category_filter_social_motive_military_inspired_lingo',
        'category_filter_social_motive_sports_terms',
        'category_filter_social_motive_communal_language',
        'category_filter_social_motive_DEIB',
        'category_filter_social_motive_positive_emotions',
    ]
]

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
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.chart_time_range') }}</div>
            <select class="dropdown margin-right" id="timerangeDropdown" onchange="setParams()">
                <option value="1w">{{ __('content.chart_time_range_week') }}</option>
                <option value="1m">{{ __('content.chart_time_range_month') }}</option>
                <option value="3m">{{ __('content.chart_time_range_quarter') }}</option>
                <option value="1y">{{ __('content.chart_time_range_year') }}</option>
                <!-- <option value="9999999">{{ __('content.chart_time_range_all') }}</option> -->
            </select>
        </div>

        <div class="drowdown-wrapper">
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.language_filter') }}</div>
            <select class="dropdown margin-right" id="languageDropdown" onchange="setParams()">
                <option value="EN">{{ __('content.language_filter_en') }}</option>
                <option value="DE">{{ __('content.language_filter_de') }}</option>
                <option value="BOTH">{{ __('content.language_filter_both') }}</option>
            </select>
        </div>

        <div class="dropdown">
            <div class="lato-small-text-p wittyworks-margin-right">{{ __('content.category_filter') }}</div>
            <div class="dropdown-content">
                <div class="sub-dropdown">
                    <?php foreach ($categoriesWithSubcategories as $category => $subcategories): ?>
                        <div onmouseover="document.getElementById('<?php echo $category; ?>').style.display = 'block';"
                            onmouseout="document.getElementById('<?php echo $category; ?>').style.display = 'none';">
                            <input type="checkbox" name="<?php echo $category; ?>" value="{{ __('content.'.$category) }}">
                            <label for="<?php echo $category; ?>">{{ __('content.'.$category) }}</label>
                        </div>
                        <div class="dropdown">
                            <div class="dropdown-content" id="<?php echo $category; ?>" style="display: none;">
                                <?php foreach ($subcategories as $subcategory): ?>
                                    <div class="sub-dropdown">
                                        <input type="checkbox" name="<?php echo $subcategory; ?>" value="{{ __('content.'.$subcategory) }}">
                                        <label for="<?php echo $subcategory; ?>">{{ __('content.'.$subcategory) }}</label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
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
            <div id="overview-wrapperNoData" style="visibility: hidden; width: 100%">
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
                  <canvas id="' . $eventsCharts[$i] . '" class="wittyworks-analytics-chart-small wittyworks-margin-right" ></canvas>
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
               <div id="top-words-content" style="visibility: hidden; width: 100%">
                  <div class="container-row wittyworks-margin-top">
                     <canvas
                        id="topWordsChart"
                        class="wittyworks-analytics-chart-extra-large wittyworks-margin-top">
                     </canvas>
                  </div>
                  <div id="topWordsChartCorporateRulesWrapper" class="chart-container-row wittyworks-margin-top" style="display: none; width: 100%; align-items:center">
                     <canvas
                        id="topWordsChartCorporateRules"
                        class="wittyworks-analytics-chart-medium wittyworks-margin-top">
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
    function load_charts(refresh, startOfWeek, chartType = 'overview', timerange = 30, interval = 'week', lang = null, categories = null, diversity_dimension_drivers = null) {
        console.log('load_charts', chartType);
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

    function aggregate_chart_data_by_week(xValues, yValues) {
        var aggregatedData = [];
        var aggregatedLabels = [];
        var currentXValue = xValues[0];
        var currentYValue = yValues[0];
        for (var i = 1; i <= xValues.length; i++) {
            if (xValues[i] == currentXValue) {
                currentYValue += yValues[i];
            } else {
                aggregatedData.push(currentYValue);
                aggregatedLabels.push( "{{ __('content.week') }}" + " " + currentXValue);
                currentXValue = xValues[i];
                currentYValue = yValues[i];
            }
        }
        return [aggregatedLabels, aggregatedData];
    }

    function handle_no_data(loadingIconId, sectionIdNoData = null) {
        document.getElementById(loadingIconId).style.display = "none";
        if (sectionIdNoData) {
            document.getElementById(sectionIdNoData).style.visibility = "visible";
            const sectionId = sectionIdNoData.replace("NoData", "");
            document.getElementById(sectionId).style.display = "none";
        }
    }

    async function getChartData(chart, from = '1m', interval = 'day', filter = null) {
        let analyticsUrl = '/api/user/analytics?refresh=' + refresh + '&chart=';
        if (window.location.href.includes('team')) {
            analyticsUrl = '/api/team/analytics?refresh=' + refresh + '&chart=';
        }
        analyticsUrl += chart + '&from=' + from + '&interval=' + interval
        if(filter) {
            analyticsUrl += '&category_filters[]=' + filter;
        }
        const response = await fetch(analyticsUrl);
        const data = await response.json();

        console.log('getChartData', chart, from, interval, filter, data);
        return data;
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

    chartType == 'overview' && getChartData('total', timerange).then(data => {
        if (!data || !data.events || !data.events.popover_open || Object.entries(data.events.popover_open).filter(([key, value]) => value > 0).length == 0) {
            handle_no_data('loadingIconActivity', 'overview-wrapperNoData');
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
                    xValuesPopoverOpenIntervallWeek.push(moment(date).startOf('week').isoWeekday(startOfWeek).week());
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
            
            const aggregatedCheckChatData =  aggregate_chart_data_by_week(xValuesPopoverOpenIntervallWeek, yValuesCheck);

            document.getElementById("checkDaysInRow").innerHTML =  '{{ __('content.writing_streak') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' +  writing_streak + '&nbsp</span>' + '{{ __('content.in_a_row') }}';
            document.getElementById("changeInLearningBitesPercentage").innerHTML =  '{{ __('content.you_clicked') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInLearningBitesPercentage + '%</span>&nbsp;' + (changeInLearningBitesPercentage >= 0 ? '{{ __('content.learning_bites_requests_week_positive') }}' : '{{ __('content.learning_bites_requests_week_negative') }}');
            document.getElementById("changeInPopoverPercentage").innerHTML =  '{{ __('content.you_explored') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInPopoverPercentage + '%</span>&nbsp;' + (changeInPopoverPercentage >= 0 ? '{{ __('content.popover_open_week_positive') }}' : '{{ __('content.popover_open_week_negative') }}');
            document.getElementById("changeInAlternativePercentage").innerHTML =  '{{ __('content.you_selected') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInAlternativePercentage + '%</span>&nbsp;' + (changeInAlternativePercentage >= 0 ? '{{ __('content.alternative_clicked_week_positive') }}' : '{{ __('content.alternative_clicked_week_negative') }}');
            document.getElementById("changeInIgnorePercentage").innerHTML =  '{{ __('content.you_ignored') }}' + '&nbsp; <span class="lato-small-paragraph-title-h4-purple">' + changeInIgnorePercentage + '%</span>&nbsp;' + (changeInIgnorePercentage >= 0 ? '{{ __('content.ignored_words_week_positive') }}' : '{{ __('content.ignored_words_week_negative') }}');

            //insert drowdown with two options to id startOfWeekDropdown
            const startOfWeekDropdown = document.getElementById("startOfWeekDropdown").innerHTML = `<select id="startOfWeek" class="dropdown" onchange="load_charts(true, this.value)">
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
                } else {
                    return document.getElementById("lastRefresh").innerHTML = '{{ __('content.last_refreshed') }} &nbsp;' + lastRefreshFormatted;
                }
            };

            updateSection();
            setInterval(function() {
                updateSection()
            }, 30000);
            document.getElementById('lastRefresh').style.visibility = 'visible';

            const aggregatedPopoverOpenData = aggregate_chart_data_by_week(xValuesPopoverOpenIntervallWeek, yValuesPopoverOpen)[1];
            const aggregatedAlternativeData = aggregate_chart_data_by_week(xValuesPopoverOpenIntervallWeek, yValuesAlternative)[1];
            const aggregatedIgnoreData = aggregate_chart_data_by_week(xValuesPopoverOpenIntervallWeek, yValuesIgnore)[1];
            const aggregatedLearningBitesData = aggregate_chart_data_by_week(xValuesPopoverOpenIntervallWeek, yValuesLearningBites)[1];

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
           
        getChartData('dau', timerange, 'week').then(data => {
        if (!data || !data.events || !data.events.popover_open || Object.entries(data.events.popover_open).filter(([key, value]) => value > 0).length == 0) {
            handle_no_data('loadingIconActivity', 'overview-wrapperNoData');
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
            document.getElementById("overview-wrapperNoData").style.display = "none";
            document.getElementById("overview-wrapper").style.visibility = "visible";
        });
        }
    });

    chartType == 'top-categories' && getChartData('topSubcategories', 7).then(data => {
        if (!data || !data.events ) {
            handle_no_data('loading-icon-top-categories');
            return;
        }
        console.log('topSubcategories', data);
        const eventsPopoverOpenedWeekUnsorted = data.events.popover_open || {};
        const openedWeek = Object.entries(eventsPopoverOpenedWeekUnsorted).sort((a, b) => b[1] - a[1]).slice(0, 8);
        for (const [key, value] of openedWeek) {
            if (key && value) {
                xTopSubCategoriesWeek.push(key);
                yTopSubCategoriesWeek.push(value);
            }
        }

        getChartData('topSubcategories', 14).then(data => {
        if (!data || !data.events ) {
            handle_no_data('loading-icon-top-categories');
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

        if (yTopSubCategoriesTwoWeeks.every((val, i, arr) => val === arr[0]) || yTopSubCategoriesWeek.every((val, i, arr) => val === arr[0])) {
            document.getElementById("categoriesRadar").style.display = "none";
            document.getElementById("loading-icon-top-categories").style.display = "none";
            document.getElementById("top-categories-wrapper").style.visibility = "visible";

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


    chartType == 'overview' && getChartData('topSubcategories', timerange).then(data => {
        if (!data || !data.events || !data.subcategories ) {
            handle_no_data('loading-icon-top-categories');
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
                         
    });

    chartType == 'top-words' && getChartData('topWords', 7).then(data => {
        if (!data || !data.events ) {
            handle_no_data('loading-icon-top-words');
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

        getChartData('topWords', 14).then(data => {
        if (!data || !data.events ) {
            handle_no_data('loading-icon-top-words');
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
            document.getElementById("loading-icon-top-words").style.display = "none";
            document.getElementById("top-words-wrapper").style.visibility = "visible";

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
        document.getElementById("loading-icon-top-words").style.display = "none";
        document.getElementById("top-words-content").style.visibility = "visible";
    });


    chartType == 'top-words' && getChartData('topWords', timerange).then(data => {
        if (!data || !data.events ) {
            handle_no_data('loading-icon-top-words');
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
       
        createBarChart(
            "topWordsChart",
            xValuesTopWordsOpenedCut,
            yValuesTopWordsOpenedCut,
            "{{ __('content.title_words_bar_chart_month') }}",
            true,
            false
        );
    });

    chartType == 'top-words' && getChartData('topWords', timerange, 'day', 'corporate_rules').then(data => {
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

function setParams() {
    const activeTab = document.getElementsByClassName("analytics-tab-line")[0].id.replace('-line', '');
    console.log('activeTab', activeTab);
    const timeRangeDropdown = document.getElementById("timerangeDropdown");
    const languageDropdown = document.getElementById("languageDropdown");

    const selectedTimeRangeOption = timeRangeDropdown.options[timeRangeDropdown.selectedIndex].value;
    const selectedLanguageOption = languageDropdown.options[languageDropdown.selectedIndex].value;

    const interval = selectedTimeRangeOption == '1w' ? 'day' : selectedTimeRangeOption == '1m' ? 'day' : selectedTimeRangeOption == '3m' ? 'week' : 'month';
 
    //refresh, startOfWeek, chartType, timerange, interval, lang, categories, diversity_dimension_drivers
    load_charts(false, 1, activeTab, selectedTimeRangeOption, interval, selectedLanguageOption);
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
            document.getElementById('loading-icon-' + tab).style.display = 'flex';
            document.getElementById('loading-icon-' + tab).style.visibility = 'visible';
            load_charts(false, 1, tab);
            document.getElementById(tab).style.display = 'block';
        }
    });    
} 
</script>