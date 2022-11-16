<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script>
    function load_charts(refresh) {
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

    const xTopSubCategoriesWeek = [];
    const yTopSubCategoriesWeek = [];
    const yTopSubCategoriesTwoWeeks = [];

    const xValuesTopSubCategoriesIgnored = [];
    const yValuesTopSubCategoriesIgnored = [];
    const xValuesTopSubCategoriesOpened = [];
    const yValuesTopSubCategoriesOpened = [];

    const xValuesTopWordsIgnored = [];
    const yValuesTopWordsIgnored = [];
    const xValuesTopWordsOpened = [];
    const yValuesTopWordsOpened = [];

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

    function formattedDate (xValuesCheck) {
        const formattedDate = xValuesCheck.map(function(x) {
            year = x.split("-")[0];
            month = x.split("-")[1];
            day = x.split("-")[2];
            return day + "." + month;
        });
        return formattedDate;
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
                },
                legend: {
                    display: display,
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
        const lastRefresh = new Date((data.last_refresh.check))
        const lastRefreshFormatted = lastRefresh.getDate() + "." + (lastRefresh.getMonth() + 1) + "." + lastRefresh.getFullYear() + " " + lastRefresh.getHours() + ":" + lastRefresh.getMinutes();
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
            }

            //CURRENT WEEK
            const yValuesCheckWeek = yValuesCheck.slice(-7);
            const xValuesCheckWeek = xValuesCheck.slice(-7);
            const yValuesPopoverOpenWeek = yValuesPopoverOpen.slice(-7);
            const yValuesIgnoreWeek = yValuesIgnore.slice(-7);
            const yValuesAlternativeWeek = yValuesAlternative.slice(-7);

            //PREVIOUS WEEK
            const yValuesCheckWeekPrevious = yValuesCheck.slice(-14, -7);
            const xValuesCheckWeekPrevious = xValuesCheck.slice(-14, -7);
            const yValuesPopoverOpenWeekPrevious = yValuesPopoverOpen.slice(-14, -7);
            const yValuesIgnoreWeekPrevious = yValuesIgnore.slice(-14, -7);
            const yValuesAlternativeWeekPrevious = yValuesAlternative.slice(-14, -7);
            
            //TOTAL WEEK
            const totalWeeklyCheck = yValuesCheckWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverOpen = yValuesPopoverOpenWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyIgnore = yValuesIgnoreWeek.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyAlternative = yValuesAlternativeWeek.map(Number).reduce((a, b) => a + b, 0);
            const weeklyEvents = [totalWeeklyCheck, totalWeeklyPopoverOpen, totalWeeklyIgnore, totalWeeklyAlternative];

            //TOTAL WEEK PREVIOUS
            const totalWeeklyCheckPrevious = yValuesCheckWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyPopoverOpenPrevious = yValuesPopoverOpenWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyIgnorePrevious = yValuesIgnoreWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const totalWeeklyAlternativePrevious = yValuesAlternativeWeekPrevious.map(Number).reduce((a, b) => a + b, 0);
            const weeklyEventsPrevious = [totalWeeklyCheckPrevious, totalWeeklyPopoverOpenPrevious, totalWeeklyIgnorePrevious, totalWeeklyAlternativePrevious];

            //WEEKLY CHANGE
            const changeInCheckPercentage = (((totalWeeklyCheck - totalWeeklyCheckPrevious) / (totalWeeklyCheckPrevious == 0 ? 1 : totalWeeklyCheckPrevious)) * 100).toFixed(2);
            const changeInPopoverPercentage = (((totalWeeklyPopoverOpen - totalWeeklyPopoverOpenPrevious) / (totalWeeklyPopoverOpenPrevious == 0 ? 1 : totalWeeklyPopoverOpenPrevious)) * 100).toFixed(2);
            const changeInIgnorePercentage = (((totalWeeklyIgnore - totalWeeklyIgnorePrevious) / (totalWeeklyIgnorePrevious == 0 ? 1 : totalWeeklyIgnorePrevious)) * 100).toFixed(2);
            const changeInAlternativePercentage = (((totalWeeklyAlternative - totalWeeklyAlternativePrevious) / (totalWeeklyAlternativePrevious == 0 ? 1 : totalWeeklyAlternativePrevious)) * 100).toFixed(2);

            document.getElementById("changeInCheckPercentage").innerHTML = changeInCheckPercentage >= 0 ? `+${changeInCheckPercentage}%&nbsp;` : `${changeInCheckPercentage}% &nbsp;`;
            document.getElementById("changeInPopoverPercentage").innerHTML = changeInPopoverPercentage >= 0 ? `+${changeInPopoverPercentage}%&nbsp;` : `${changeInPopoverPercentage}% &nbsp;`;
            document.getElementById("changeInIgnorePercentage").innerHTML = changeInIgnorePercentage >= 0 ? `+${changeInIgnorePercentage}%&nbsp;` : `${changeInIgnorePercentage}% &nbsp;`;
            document.getElementById("changeInAlternativePercentage").innerHTML = changeInAlternativePercentage >= 0 ? `+${changeInAlternativePercentage}%&nbsp;` : `${changeInAlternativePercentage}% &nbsp;`;
            document.getElementById("checkDaysInRow").innerHTML = getWittyStreak(yValuesCheck) + '&nbsp';

            const lastRefreshMinutes = Math.floor((new Date() - lastRefresh) / 60000);

            if (lastRefreshMinutes > 3) {
                document.getElementById("lastRefresh").innerHTML = '<?php echo __('content.last_refreshed') ?> &nbsp;' + lastRefreshFormatted + '&nbsp; &nbsp; <a class="button primary-button-red" onclick="load_charts(true)"><?php echo __('content.refresh_data') ?></a>';
            } else {
                document.getElementById("lastRefresh").innerHTML = '<?php echo __('content.last_refreshed') ?> &nbsp;' + lastRefreshFormatted + '&nbsp; &nbsp; <a class="button secondary-button-red wittyworks-margin-right" style="cursor:not-allowed"><?php echo __('content.refresh_data') ?></a><span class="tooltiptext"><?php echo __('content.refresh_data_blocked') ?></span>';
            }

            new Chart("eventsChart", {
                type: "line",
                data: {
                    labels: formattedDate(xValuesCheck),
                    datasets: [{
                        data: yValuesCheck,
                        borderColor: colors[3],
                        fill: false,
                        label: "<?php echo __('content.check_label_line_chart') ?>",
                        }, {
                        data: yValuesPopoverOpen,
                        borderColor: colors[6],
                        fill: false,
                        label: "<?php echo __('content.popover_label_line_chart') ?>",
                        }, {
                        data: yValuesIgnore,
                        borderColor: colors[9],
                        fill: false,
                        label:  "<?php echo __('content.ignored_label_line_chart') ?>",
                        },{
                        data: yValuesAlternative,
                        borderColor: colors[12],
                        fill: false,
                        label: "<?php echo __('content.alternative_label_line_chart') ?>",
                    }]
                },
                options: {
                    title: {
                    display: true,
                    text: "<?php echo __('content.title_line_chart') ?>",
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
            });

            createBarChart(
                "eventsCheckChart",
                xValuesCheckWeek,
                yValuesCheckWeek,
                "<?php echo __('content.title_bar_chart_check') ?>",
                false,
                true
            );

            createBarChart(
                "eventsPopoverChart",
                xValuesCheckWeek,
                yValuesPopoverOpenWeek,
                "<?php echo __('content.title_bar_chart_popover') ?>",
                false,
                true
            );

            createBarChart(
                "eventsIgnoreChart",
                xValuesCheckWeek,
                yValuesIgnoreWeek,
                "<?php echo __('content.title_bar_chart_ignored') ?>",
                false,
                true
            );

            createBarChart(
                "eventsAlternativeChart",
                xValuesCheckWeek,
                yValuesAlternativeWeek,
                "<?php echo __('content.title_bar_chart_alternative') ?>",
                false,
                true
            );

            createDoughnutChart(
                "requestRatiosChartDoughnut",
                [
                "<?php echo __('content.check_doughnut_chart_event_ratio') ?>",
                "<?php echo __('content.popover_doughnut_chart_event_ratio') ?>",
                "<?php echo __('content.ignored_doughnut_chart_event_ratio') ?>",
                "<?php echo __('content.alternative_doughnut_chart_event_ratio') ?>"
                ],
                weeklyEvents,
                "<?php echo __('content.title_doughnut_chart_event_ratio') ?>",
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
        let formattedLabels = xTopSubCategoriesWeek.map(x => x.replace(/_/g, ' '));
        formattedLabels = formattedLabels.map(x => x.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();}));
        const dataCategories = {
                labels: formattedLabels,
                datasets: [{
                    label: "<?php echo __('content.title_categories_radar_chart_last_week') ?>",
                    data: yTopSubCategoriesTwoWeeks,
                    fill: true,
                    backgroundColor: 'hsla(247, 52.8%, 75.9%, 0.5)',
                    borderColor: 'hsla(248, 53.2%, 60.6%, 0.5)'
                }, {
                    label: "<?php echo __('content.title_categories_radar_chart_current_week') ?>",
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

        for (const [key, value] of Object.entries(ignored)) {
            xValuesTopSubCategoriesIgnored.push(key);
            yValuesTopSubCategoriesIgnored.push(value);
        }

        for (const [key, value] of Object.entries(opened)) {
            xValuesTopSubCategoriesOpened.push(key);
            yValuesTopSubCategoriesOpened.push(value);
        }

        const xValuesTopSubCategoriesIgnoredCutDoughnut = xValuesTopSubCategoriesIgnored.slice(0, 5);
        const yValuesTopSubCategoriesIgnoredCutDoughnut = yValuesTopSubCategoriesIgnored.slice(0, 5);

        const xValuesTopSubCategoriesOpenedCut = xValuesTopSubCategoriesOpened.slice(0, 15);
        const yValuesTopSubCategoriesOpenedCut = yValuesTopSubCategoriesOpened.slice(0, 15);
        const xValuesTopSubCategoriesOpenedCutDoughnut = xValuesTopSubCategoriesOpened.slice(0, 5);
        const yValuesTopSubCategoriesOpenedCutDoughnut = yValuesTopSubCategoriesOpened.slice(0, 5);

        createBarChart(
            "topSubCategoriesChart",
            xValuesTopSubCategoriesOpenedCut,
            yValuesTopSubCategoriesOpenedCut,
            "<?php echo __('content.title_categories_bar_chart_month') ?>",
            false,
            false
        );

        createDoughnutChart(
            "topSubCategoriesOpenedChartDoughnut",
            xValuesTopSubCategoriesOpenedCutDoughnut,
            yValuesTopSubCategoriesOpenedCutDoughnut,
            "<?php echo __('content.title_categories_opened_doughnut_chart_month') ?>",

        );
    
        createDoughnutChart(
            "topSubCategoriesIgnoredChartDoughnut",
            xValuesTopSubCategoriesIgnoredCutDoughnut,
            yValuesTopSubCategoriesIgnoredCutDoughnut,
            "<?php echo __('content.title_categories_ignored_doughnut_chart_month') ?>",
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

        let formattedLabels = xTopWordsWeek.map(x => x.replace(/_/g, ' '));
        formattedLabels = formattedLabels.map(x => x.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();}));

        const dataCategories = {
            labels:
            formattedLabels,
            datasets: [{
                label: "<?php echo __('content.title_words_radar_chart_last_week') ?>",
                data: yTopWordsTwoWeeks,
                fill: true,
                backgroundColor: 'hsla(247, 52.8%, 75.9%, 0.5)',
                    borderColor: 'hsla(248, 53.2%, 60.6%, 0.5)'
            }, {
                label: "<?php echo __('content.title_words_radar_chart_current_week') ?>",
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

        for (const [key, value] of Object.entries(ignored)) {
            xValuesTopWordsIgnored.push(key);
            yValuesTopWordsIgnored.push(value);
        }

        for (const [key, value] of Object.entries(opened)) {
            xValuesTopWordsOpened.push(key);
            yValuesTopWordsOpened.push(value);
        }

        const xValuesTopWordsIgnoredCutDoughnut = xValuesTopWordsIgnored.slice(0, 5);
        const yValuesTopWordsIgnoredCutDoughnut = yValuesTopWordsIgnored.slice(0, 5);

        const xValuesTopWordsOpenedCut = xValuesTopWordsOpened.slice(0, 15);
        const yValuesTopWordsOpenedCut = yValuesTopWordsOpened.slice(0, 15);
        const xValuesTopWordsOpenedCutDoughnut = xValuesTopWordsOpened.slice(0, 5);
        const yValuesTopWordsOpenedCutDoughnut = yValuesTopWordsOpened.slice(0, 5);

        createDoughnutChart(
            "topWordsChartDoughnut",
            xValuesTopWordsOpenedCutDoughnut,
            yValuesTopWordsOpenedCutDoughnut,
            "<?php echo __('content.title_words_opened_doughnut_chart_month') ?>",
        );

        createDoughnutChart(
            "topWordsChartDoughnutWeek",
            xValuesTopWordsIgnoredCutDoughnut,
            yValuesTopWordsIgnoredCutDoughnut,
            "<?php echo __('content.title_words_ignored_doughnut_chart_month') ?>",
        );
       
        createBarChart(
            "topWordsChart",
            xValuesTopWordsOpenedCut,
            yValuesTopWordsOpenedCut,
            "<?php echo __('content.title_words_bar_chart_month') ?>",
            false,
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
                <div id="lastRefresh" class="lato-small-text-p wittyworks-margin-right container-row tooltip" style="align-items: center;"></div>
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
                                        id="changeInCheckPercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">{{ __('content.check_requests_week') }}</div>
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
                            <canvas
                                id="eventsCheckChart"
                                style="max-width: 195px"
                                class="wittyworks-analytics-chart-small wittyworks-margin-right">
                            </canvas>
                            <canvas
                                id="eventsPopoverChart"
                                style="max-width: 195px"
                                class="wittyworks-analytics-chart-small wittyworks-margin-right">
                            </canvas>
                            <canvas
                                id="eventsIgnoreChart"
                                style="max-width: 195px"
                                class="wittyworks-analytics-chart-small wittyworks-margin-right">
                            </canvas>
                            <canvas
                                id="eventsAlternativeChart"
                                style="max-width: 195px"
                                class="wittyworks-analytics-chart-small">
                            </canvas>
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