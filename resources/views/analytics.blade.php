<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js" rossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    function load_charts(refresh) {
        if (refresh) {
            document.getElementById('lastRefresh').style.visibility = 'hidden';
            document.getElementById("loadingIconActivity").style.display = "flex";
            document.getElementById("activityChartWrapper").style.visibility = "hidden";

            document.getElementById("loadingIconTopWords").style.display = "flex";
            document.getElementById("topWordsChartWrapper").style.visibility = "hidden";

            document.getElementById("loadingIconTopCatagories").style.display = "flex";
            document.getElementById("topCategoriesChartWrapper").style.visibility = "hidden";
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

    Chart.defaults.global.defaultFontFamily = 'Lato';
    Chart.defaults.global.defaultFontColor = '#000000';

    function getWittyStreak (yValuesCheck) {
        let checkDaysInRow = 0;
        for (let i = yValuesCheck.length - 1; i >= 0; i--) {
            if (yValuesCheck[i] != '0') {
                checkDaysInRow++;
            } else {
                break;
            }
        }
        return checkDaysInRow;
    }

    async function getCharttData(chart, interval = 30) {
        let analyticsUrl = '/api/user/analytics?refresh=' + refresh + '&chart=';
        if (window.location.href.includes('team')) {
            analyticsUrl = '/api/team/analytics?refresh=' + refresh + '&chart=';
        }
        const response = await fetch(
            analyticsUrl + chart + '&interval=' + interval);
        const data = await response.json();
        return data;
    }

    function createBarChart(chartId, xValues, yValues, text, display, singeColor) {
        new Chart(chartId, {
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
                maintainAspectRatio: false,
                title: {
                display: true,
                text: text,
                fontSize: 16,
                fontStyle: 'normal'
                },
                scales:{
                    xAxes: [{
                        display: display
                    }],
                    yAxes: [{
                        ticks: {
                            min: 0,
                            beginAtZero: true,
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
        new Chart(chartId, {
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
                    title: {
                    display: true,
                    text: text,
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
        });
    }

    getCharttData('total').then(data => {
        const events = data.events;
        const lastRefresh = data.events.last_refresh;
        let lastRefreshFormatted = moment(lastRefresh).fromNow();

        for (const [event, value] of Object.entries(events)) {
            if (event === 'check') {
               for (const [date, count] of Object.entries(value)) {
                    xValuesCheck.push(date);
                    yValuesCheck.push(count);
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
            } else if (event === 'popover_open') {
                for (const [date, count] of Object.entries(value)) {
                    xValuesPopoverOpen.push(date);
                    yValuesPopoverOpen.push(count);
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
            const totalWeeklyLearningBites = yValuesLearningBites.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverOpen = yValuesPopoverOpenWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyIgnore = yValuesIgnoreWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyAlternative = yValuesAlternativeWeek.map(Number).reduce((a, b) => a + b, 0);
            const weeklyEvents = [totalWeeklyLearningBites, totalWeeklyPopoverOpen, totalWeeklyIgnore, totalWeeklyAlternative];

            //TOTAL WEEK PREVIOUS
            const totalWeeklyLearningBitesPrevious = yValuesLearningBitesWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverOpenPrevious = yValuesPopoverOpenWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyIgnorePrevious = yValuesIgnoreWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyAlternativePrevious = yValuesAlternativeWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const weeklyEventsPrevious = [totalWeeklyLearningBitesPrevious, totalWeeklyPopoverOpenPrevious, totalWeeklyIgnorePrevious, totalWeeklyAlternativePrevious];

            //WEEKLY CHANGE
            const changeInLearningBitesPercentage = (((totalWeeklyLearningBites - totalWeeklyLearningBitesPrevious) / (totalWeeklyLearningBitesPrevious == 0 ? 1 : totalWeeklyLearningBitesPrevious)) * 100).toFixed(0);
            const changeInPopoverPercentage = (((totalWeeklyPopoverOpen - totalWeeklyPopoverOpenPrevious) / (totalWeeklyPopoverOpenPrevious == 0 ? 1 : totalWeeklyPopoverOpenPrevious)) * 100).toFixed(0);
            const changeInIgnorePercentage = (((totalWeeklyIgnore - totalWeeklyIgnorePrevious) / (totalWeeklyIgnorePrevious == 0 ? 1 : totalWeeklyIgnorePrevious)) * 100).toFixed(0);
            const changeInAlternativePercentage = (((totalWeeklyAlternative - totalWeeklyAlternativePrevious) / (totalWeeklyAlternativePrevious == 0 ? 1 : totalWeeklyAlternativePrevious)) * 100).toFixed(0);

            
            document.getElementById("changeInLearningBitesPercentage").innerHTML = changeInLearningBitesPercentage >= 0 ? `+${changeInLearningBitesPercentage}%&nbsp;` : `${changeInLearningBitesPercentage}%&nbsp;`;
            document.getElementById("changeInPopoverPercentage").innerHTML = changeInPopoverPercentage >= 0 ? `+${changeInPopoverPercentage}%&nbsp;` : `${changeInPopoverPercentage}% &nbsp;`;
            document.getElementById("changeInIgnorePercentage").innerHTML = changeInIgnorePercentage >= 0 ? `+${changeInIgnorePercentage}%&nbsp;` : `${changeInIgnorePercentage}% &nbsp;`;
            document.getElementById("changeInAlternativePercentage").innerHTML = changeInAlternativePercentage >= 0 ? `+${changeInAlternativePercentage}%&nbsp;` : `${changeInAlternativePercentage}% &nbsp;`;
            document.getElementById("checkDaysInRow").innerHTML = getWittyStreak(yValuesCheck) + '&nbsp';

            //updateSection function
            function updateSection() {
                const lastRefreshMinutes = Math.floor((new Date() - lastRefresh) / 60000);
                lastRefreshFormatted = moment(lastRefresh).fromNow();
                if (lastRefreshMinutes >= 3) {
                    return document.getElementById("lastRefresh").innerHTML = '{{ __('content.last_refreshed') }} &nbsp;' + lastRefreshFormatted + '&nbsp; &nbsp; <a class="button primary-button-red" onclick="load_charts(true)">{{ __('content.refresh_data') }}</a>';
                } else {
                    return document.getElementById("lastRefresh").innerHTML = '{{ __('content.last_refreshed') }} &nbsp;' + lastRefreshFormatted;
                }
            };

            updateSection();
            setInterval(function() {
                updateSection()
            }, 30000);
            document.getElementById('lastRefresh').style.visibility = 'visible';

            new Chart("eventsChart", {
                type: "line",
                data: {
                    labels: xValuesPopoverOpen,
                    datasets: [
                        {
                            data: yValuesPopoverOpen,
                            borderColor: colors[6],
                            fill: false,
                            label: "{{ __('content.popover_label_line_chart') }}",
                        },
                        {
                            data: yValuesAlternative,
                            borderColor: colors[12],
                            fill: false,
                            label: "{{ __('content.alternative_label_line_chart') }}",
                        },
                        {
                            data: yValuesIgnore,
                            borderColor: colors[9],
                            fill: false,
                            label:  "{{ __('content.ignored_label_line_chart') }}",
                        },
                        {
                            data: yValuesLearningBites,
                            borderColor: colors[3],
                            fill: false,
                            label: "{{ __('content.learning_bites_label_line_chart') }}",
                        },
                    ]
                },
                options: {
                    title: {
                    display: true,
                    text: "{{ __('content.title_line_chart') }}",
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
            });

            createBarChart(
                "eventsLearningBitesChart",
                xValuesLearningBitesWeek,
                yValuesLearningBitesWeek,
                "{{ __('content.title_bar_chart_learning_bites') }}",
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
                "{{ __('content.learning_bites_doughnut_chart_event_ratio') }}",
                "{{ __('content.popover_doughnut_chart_event_ratio') }}",
                "{{ __('content.ignored_doughnut_chart_event_ratio') }}",
                "{{ __('content.alternative_doughnut_chart_event_ratio') }}"
                ],
                weeklyEvents,
                "{{ __('content.title_doughnut_chart_event_ratio') }}",
            );
    
            document.getElementById("loadingIconActivity").style.display = "none";
            document.getElementById("activityChartWrapper").style.visibility = "visible";
        }
    });

    getCharttData('topSubcategories', 7).then(data => {
        const openedWeek = Object.entries(data.events.popover_open).sort((a, b) => b[1] - a[1]).slice(0, 8);
        for (const [key, value] of openedWeek) {
            if (key && value) {
                xTopSubCategoriesWeek.push(key);
                yTopSubCategoriesWeek.push(value);
            }
        }

        getCharttData('topSubcategories', 14).then(data => {
        const openedTwoWeeks = data.events.popover_open;
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
  
            new Chart("categoriesRadar", {
                type: 'radar',
                    data: dataCategories,
                    options: {
                        elements: {
                        line: {
                            borderWidth: 1
                        },
                        opacity: 0.5

                    }
                },
            });
            document.getElementById("loadingIconTopCatagories").style.display = "none";
            document.getElementById("topCategoriesChartWrapper").style.visibility = "visible";
        });
    });


    getCharttData('topSubcategories').then(data => {
        const ignored = data.events.ignore;
        const opened = data.events.popover_open;
        const alternative = data.events.alternative;

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

    getCharttData('topWords', 7).then(data => {
        const xTopWordsWeek = [];
        const yTopWordsWeek = [];
        const yTopWordsTwoWeeks = [];
        const openedWeek = Object.entries(data.events.popover_open).sort((a, b) => b[1] - a[1]).slice(0, 8);
        for (const [key, value] of openedWeek) {
            if (key && value) {
                xTopWordsWeek.push(key);
                yTopWordsWeek.push(value);
            }
        }

        getCharttData('topWords', 14).then(data => {
        const openedTwoWeeks = data.events.popover_open;
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
    
        new Chart("wordsRadar", {
            type: 'radar',
            data: dataCategories,
            options: {
                elements: {
                    line: {
                        borderWidth: 1
                        }
                    }
                },
            });
            document.getElementById("loadingIconTopWords").style.display = "none";
            document.getElementById("topWordsChartWrapper").style.visibility = "visible";
        });
    });


    getCharttData('topWords').then(data => {
        const ignored = data.events.ignore;
        const opened = data.events.popover_open;
        const alternative = data.events.alternative;

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
};
load_charts(false);
</script>

<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page lg:ml-20">
                <div id="lastRefresh" class="lato-small-text-p wittyworks-margin-right container-row" style="visibility: hidden; align-items: center;"></div>
                <div class="ibarra-sub-title-h2">{{ __('content.activity') }}</div>
                <div class="wittyworks-form-section container border-radius">
                    <div id="loadingIconActivity" class="loading-icon-wrapper" style="width: 100%">
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
                    <div id="activityChartWrapper" style="visibility: hidden; width: 100%">
                        <div class="container-row wittyworks-margin-top">
                            <div class="container-column" style="margin-left: 2em;">
                                <div class="container-row" style="align-items: center">
                                    <div id="checkDaysInRow" class="ibarra-sub-title-h2-purple"></div>
                                    <div class="lato-small-text-p">{{ __('content.writing_streak') }}</div>
                                </div>

                                <div class="container-row  wittyworks-margin-top" >
                                    <div
                                        id="changeInLearningBitesPercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">{{ __('content.learning_bites_requests_week') }}</div>
                                </div>
                                
                                <div class="container-row" >
                                    <div id="changeInPopoverPercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">{{ __('content.popover_open_week') }}</div>
                                </div>

                                <div class="container-row" >
                                    <div id="changeInAlternativePercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">{{ __('content.alternative_clicked_week') }}</div>
                                </div>

                                <div class="container-row">
                                    <div
                                        id="changeInIgnorePercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p margin-bottom">{{ __('content.ignored_words_week') }}</div>
                                </div>
                                
                            </div>
                            <canvas
                                id="requestRatiosChartDoughnut"
                                class="wittyworks-analytics-chart-medium"
                                style="margin-left: auto;">
                            </canvas>
                        </div>
                        <div class="container-row wittyworks-margin-top">
                            <canvas id="eventsChart" class="wittyworks-analytics-chart-extra-large"></canvas>
                        </div>
                        <div class="container-row wittyworks-margin-top">
                            @php
                                $eventsCharts = ["eventsPopoverChart", "eventsAlternativeChart", "eventsIgnoreChart", "eventsLearningBitesChart"];
                                for ($i = 0; $i < count($eventsCharts); $i++) {
                                    echo '<canvas id="' . $eventsCharts[$i] . '" style="max-width: 190px" class="wittyworks-analytics-chart-small wittyworks-margin-right" ></canvas>';
                                }
                            @endphp
                        </div>
                    </div>
                </div>

                <div class="ibarra-sub-title-h2 wittyworks-margin-top">{{ __('content.top_categories') }}</div>
                <div class="wittyworks-form-section container border-radius">
                    <div id="loadingIconTopCatagories" class="loading-icon-wrapper"  style="width: 100%">
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

                    <div id="topCategoriesChartWrapper" style="visibility: hidden; width: 100%">
                        <div class="chart-container-row wittyworks-margin-top">
                            <canvas
                                id="categoriesRadar"
                                class="wittyworks-analytics-chart-medium-radar"
                            >
                            </canvas>
                            <div class="container-column">
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
                        <div class="container-row wittyworks-margin-top">
                            <canvas
                                id="topSubCategoriesChart"
                                class="wittyworks-analytics-chart-top wittyworks-margin-top">
                            </canvas>
                        </div>
                    </div>
                </div>

                <div class="ibarra-sub-title-h2 wittyworks-margin-top">{{ __('content.top_words') }}</div>
                    <div class="wittyworks-form-section container border-radius">
                        <div id="loadingIconTopWords" class="loading-icon-wrapper" style="width: 100%">
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
                        <div id="topWordsChartWrapper" style="visibility: hidden; width: 100%">
                            <div class="chart-container-row wittyworks-margin-top">
                                <canvas
                                    id="wordsRadar"
                                    class="wittyworks-analytics-chart-medium-radar">
                                </canvas>
                                <div class="container-column">
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
                            <div class="container-row wittyworks-margin-top">
                                <canvas
                                    id="topWordsChart"
                                    class="wittyworks-analytics-chart-top wittyworks-margin-top">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>