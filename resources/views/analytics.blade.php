<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
<script>
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

    async function getCharttData(chart) {
        const response = await fetch('https://dashboard.lndo.site/api/user/analytics?refresh=1&chart=' + chart);
        const data = await response.json();
        return data;
    }

    getCharttData('total').then(data => {
        const xValuesCheck = [];
        const yValuesCheck = [];
        const xValuesPopoverOpen = [];
        const yValuesPopoverOpen = [];
        const xValuesIgnore = [];
        const yValuesIgnore = [];
        const xValuesAlternative = [];
        const yValuesAlternative = [];

        const events = data.events;
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
            document.getElementById("checkDaysInRow").innerHTML = getWittyStreak(yValuesCheck);

            Chart.defaults.global.defaultFontFamily = 'Lato';
            Chart.defaults.global.defaultFontColor = '#000000';

            new Chart("eventsChart", {
                type: "line",
                data: {
                    labels: formattedDate(xValuesCheck),
                    datasets: [{
                        data: yValuesCheck,
                        borderColor: colors[3],
                        fill: false,
                        label: 'Check',
                        }, {
                        data: yValuesPopoverOpen,
                        borderColor: colors[6],
                        fill: false,
                        label: 'Popover Open',
                        }, {
                        data: yValuesIgnore,
                        borderColor: colors[9],
                        fill: false,
                        label: 'Ignore',
                        },{
                        data: yValuesAlternative,
                        borderColor: colors[12],
                        fill: false,
                        label: 'Alternative',
                    }]
                },
                options: {
                    title: {
                    display: true,
                    text: 'Events',
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
            });

            new Chart("eventsCheckChart", {
                type: "bar",
                data: {
                    labels: xValuesCheckWeek,
                    datasets: [{
                        data: yValuesCheckWeek,
                        backgroundColor: colors[10],
                        fill: false,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    title: {
                    display: true,
                    text: 'Check (week)',
                    fontSize: 16,
                    fontStyle: 'normal'
                    },
                    scales:{
                        xAxes: [{
                            display: false //this will remove only the label
                        }],
                    },
                    legend: {
                        display: false,
                    }
                }
            });

            new Chart("eventsPopoverChart", {
                type: "bar",
                data: {
                    labels: xValuesCheckWeek,
                    datasets: [{
                        data: yValuesPopoverOpenWeek,
                        backgroundColor: colors[10],
                        fill: false,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    title: {
                    display: true,
                    text: 'Popover Open (week)',
                    fontSize: 16,
                    fontStyle: 'normal'
                    },
                    scales:{
                        xAxes: [{
                            display: false //this will remove only the label
                        }],
                    },
                    legend: {
                        display: false,
                    }
                }
            });
        
            new Chart("eventsIgnoreChart", {
                type: "bar",
                data: {
                    labels: xValuesCheckWeek,
                    datasets: [{
                        data: yValuesIgnoreWeek,
                        backgroundColor: colors[10],
                        fill: false,
                    },
                ]
                },
                options: {
                    maintainAspectRatio: false,
                    title: {
                    display: true,
                    text: 'Ignore (week)',
                    fontSize: 16,
                    fontStyle: 'normal'
                    },
                    scales:{
                        xAxes: [{
                            display: false //this will remove only the label
                        }],
                    },
                    legend: {
                        display: false,
                    }
                }
            });

            new Chart("eventsAlternativeChart", {
                type: "bar",
                data: {
                    labels: xValuesCheckWeek,
                    datasets: [{
                        data: yValuesAlternativeWeek,
                        backgroundColor: colors[10],
                        fill: false,
                    }]
                },
                options: {
                    maintainAspectRatio: false,
                    title: {
                    display: true,
                    text: 'Alternative (week)',
                    fontSize: 16,
                    fontStyle: 'normal'
                    },
                    scales:{
                        xAxes: [{
                            display: false //this will remove only the label
                        }],
                    },
                    legend: {
                        display: false,
                    },
                }
            });

            new Chart("requestRatiosChartDoughnut", {
                type: "doughnut",
                data: {
                    labels: ['Check', 'Popover Open', 'Ignore', 'Alternative'],
                    datasets: [{
                        backgroundColor: [colors[8], colors[10],colors[12], colors[14]],
                        data: weeklyEvents
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                        display: true,
                        text: "Event ratio (week)",
                        fontSize: 16,
                        fontStyle: 'normal'
                    }
                }
            });
    
            document.getElementById("loadingIconActivity").style.display = "none";
            document.getElementById("activityChartWrapper").style.visibility = "visible";
        }
    });

    getCharttData('topSubcategories').then(data => {
        const xValuesTopSubCategoriesIgnored = [];
        const yValuesTopSubCategoriesIgnored = [];
        const xValuesTopSubCategoriesOpened = [];
        const yValuesTopSubCategoriesOpened = [];

        const ignored = data.events.ignore;
        const opened = data.events.popover_open;

        console.log(data);

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

                new Chart("topSubCategoriesChart", {
                type: "bar",
                data: {
                    labels: xValuesTopSubCategoriesOpenedCut,
                    datasets: [{
                    backgroundColor: colors,
                    data: yValuesTopSubCategoriesOpenedCut
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top Subcategories opened (month)",
                    fontSize: 16,
                    fontStyle: 'normal',
                    }
                }
                });

                new Chart("topSubCategoriesOpenedChartDoughnut", {
                type: "doughnut",
                data: {
                    labels: xValuesTopSubCategoriesOpenedCutDoughnut,
                    datasets: [{
                    backgroundColor: [colors[6], colors[8], colors[10],colors[12], colors[14]],
                    data: yValuesTopSubCategoriesOpenedCutDoughnut
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top Subcategories opened",
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
                });
             
                new Chart("topSubCategoriesIgnoredChartDoughnut", {
                type: "doughnut",
                data: {
                    labels: xValuesTopSubCategoriesIgnoredCutDoughnut,
                    datasets: [{
                    backgroundColor: [colors[6], colors[8], colors[10],colors[12], colors[14]],
                    data: yValuesTopSubCategoriesIgnoredCutDoughnut
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top Subcategories ignored",
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
                });
                
                const dataCategories = { 
                labels: [
                    'Eating',
                    'Drinking',
                    'Sleeping',
                    'Designing',
                    'Coding',
                    'Cycling',
                    'Running'
                ],
                datasets: [{
                    label: 'Categories Last Week',
                    data: [65, 59, 90, 81, 56, 55, 40],
                    fill: true,
                    backgroundColor: colors[10],
                    borderColor: colors[7],
                }, {
                    label: 'Categories This Week',
                    data: [28, 48, 40, 19, 96, 27, 100],
                    fill: true,
                    backgroundColor: colors[13],
                    borderColor: colors[16],
                }]
                };
    
            new Chart("categoriesRadar", {
                type: 'radar',
                    data: dataCategories,
                    options: {
                        elements: {
                        line: {
                            borderWidth: 3
                        }
                    }
                },
            });

            document.getElementById("loadingIconTopCatagories").style.display = "none";
            document.getElementById("topCategoriesChartWrapper").style.visibility = "visible";
    });

    getCharttData('topWords').then(data => {
        const xValuesTopWordsIgnored = [];
        const yValuesTopWordsIgnored = [];
        const xValuesTopWordsOpened = [];
        const yValuesTopWordsOpened = [];
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

    

             // <!-- TODO: radar how the categories changed over time -->
             const dataWords = { 
                labels: [
                    'Eating',
                    'Drinking',
                    'Sleeping',
                    'Designing',
                    'Coding',
                    'Cycling',
                    'Running'
                ],
                datasets: [{
                    label: 'Words Last Week',
                    data: [65, 59, 90, 81, 56, 55, 40],
                    fill: true,
                    backgroundColor: colors[10],
                    borderColor: colors[7],
                }, {
                    label: 'Words This Week',
                    data: [28, 48, 40, 19, 96, 27, 100],
                    fill: true,
                    backgroundColor: colors[13],
                    borderColor: colors[16],
                }]
                };
    
                new Chart("wordsRadar", {
                type: 'radar',
                    data: dataWords,
                    options: {
                        elements: {
                        line: {
                            borderWidth: 3
                        }
                        }
                    },
                    });
            

            new Chart("topWordsChartDoughnut", {
            type: "doughnut",
            data: {
                labels: xValuesTopWordsOpenedCutDoughnut,
                datasets: [{
                backgroundColor: [colors[3], colors[6], colors[9],colors[12], colors[14]],
                data: yValuesTopWordsOpenedCutDoughnut
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top Words Opened",
                fontSize: 16,
                fontStyle: 'normal'
                }
            }
            });


            new Chart("topWordsChartDoughnutWeek", {
            type: "doughnut",
            data: {
                labels: xValuesTopWordsIgnoredCutDoughnut,
                datasets: [{
                backgroundColor: [colors[3], colors[6], colors[9],colors[12], colors[14]],
                data: yValuesTopWordsIgnoredCutDoughnut
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top Words Ignored",
                fontSize: 16,
                fontStyle: 'normal'
                }
            }
            });

            new Chart("topWordsChart", {
            type: "bar",
            data: {
                labels: xValuesTopWordsOpenedCut,
                datasets: [{
                backgroundColor: colors,
                data: yValuesTopWordsOpenedCut
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top Words opened",
                fontSize: 16,
                fontStyle: 'normal'
                }
            }
            });
            document.getElementById("loadingIconTopWords").style.display = "none";
            document.getElementById("topWordsChartWrapper").style.visibility = "visible";
    });
</script>



<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page lg:ml-20" style="width: 100%;">

                <div class="ibarra-sub-title-h2">Activity</div>
                <div class="wittyworks-form-section container border-radius">
                    <div id="loadingIconActivity" class="loading-icon-wrapper">
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
                    <div id="activityChartWrapper" style="visibility: hidden;">
                        <div class="container-row wittyworks-margin-top">
                            <div class="container-column" style="margin-left: 2em;">
                                <div class="container-row" style="align-items: center">
                                    <div id="checkDaysInRow" class="ibarra-sub-title-h2-purple"></div>
                                    <div class="lato-small-text-p">&nbsp; Days Witty writing streak</div>
                                </div>

                                <div class="container-row  wittyworks-margin-top" >
                                    <div
                                        id="changeInCheckPercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">Check requests (week)</div>
                                </div>

                                <div class="container-row" >
                                    <div id="changeInPopoverPercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">Popover Open (week)</div>
                                </div>

                                <div class="container-row" >
                                    <div id="changeInAlternativePercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p">Alternative clicked (week)</div>
                                </div>

                                <div class="container-row">
                                    <div
                                        id="changeInIgnorePercentage"
                                        class="lato-small-paragraph-title-h4-purple">
                                    </div>
                                    <div class="lato-small-text-p margin-bottom">Ignored words (week)</div>
                                </div>
                                
                            </div>
                            <canvas
                                id="requestRatiosChartDoughnut"
                                class="wittyworks-analytics-chart-medium"
                                style="margin-left: auto; margin-right: -5em">
                            </canvas>
                        </div>
                        <div class="container-row wittyworks-margin-top">
                            <canvas id="eventsChart" class="wittyworks-analytics-chart-extra-large"></canvas>
                        </div>
                        <div class="container-row wittyworks-margin-top">
                            <canvas
                                id="eventsCheckChart"
                                class="wittyworks-analytics-chart-small wittyworks-margin-right">
                            </canvas>
                            <canvas
                                id="eventsPopoverChart"
                                class="wittyworks-analytics-chart-small wittyworks-margin-right">
                            </canvas>
                            <canvas
                                id="eventsIgnoreChart"
                                class="wittyworks-analytics-chart-small wittyworks-margin-right">
                            </canvas>
                            <canvas
                                id="eventsAlternativeChart"
                                class="wittyworks-analytics-chart-small">
                            </canvas>
                        </div>
                    </div>
                </div>

                <div class="ibarra-sub-title-h2 wittyworks-margin-top">Top Categories</div>
                <div class="wittyworks-form-section container border-radius">
                    <div id="loadingIconTopCatagories" class="loading-icon-wrapper">
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
                        <div class="container-row wittyworks-margin-top">
                            <canvas
                                id="categoriesRadar"
                                class="wittyworks-analytics-chart-medium-radar"
                                style="margin-left: -5em">
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
                                class="wittyworks-analytics-chart-extra-large wittyworks-margin-top">
                            </canvas>
                        </div>
                    </div>
                </div>

                <div class="ibarra-sub-title-h2 wittyworks-margin-top">Top words</div>
                    <div class="wittyworks-form-section container border-radius">
                        <div id="loadingIconTopWords" class="loading-icon-wrapper">
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
                            <div class="container-row wittyworks-margin-top">
                                <canvas
                                    id="wordsRadar"
                                    class="wittyworks-analytics-chart-medium-radar"
                                    style="margin-left: -5em;">
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
                                    class="wittyworks-analytics-chart-extra-large wittyworks-margin-top">
                                </canvas>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-app-layout>
