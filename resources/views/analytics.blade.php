
<script>
    const xValuesTopSubCategories = [];
    const yValuesTopSubCategories = [];
    const xValuesTopWords = [];
    const yValuesTopWords = [];
    const xValuesCheck = [];
    const yValuesCheck = [];
    const xValuesPopoverOpen = [];
    const yValuesPopoverOpen = [];
    const xValuesIgnore = [];
    const yValuesIgnore = [];
    const xValuesAlternative = [];
    const yValuesAlternative = [];
    </script>

@php
                $currentURL = URL::current();
                $currentURL.= strpos('?', $currentURL) === false ? '?' : '&';
            @endphp
          
            @foreach ($topSubcategories['events'] as $event => $values)
                @foreach ($values as $word => $count)
                    <script>
                        xValuesTopSubCategories.push("{{$word}}");
                        yValuesTopSubCategories.push("{{$count}}");
                    </script>
                @endforeach
            @endforeach

            @foreach ($topWords['events'] as $event => $values)
                @foreach ($values as $word => $count)
                    <script>
                        xValuesTopWords.push("{{$word}}");
                        yValuesTopWords.push("{{$count}}");
                    </script>
                @endforeach
            @endforeach

            <!-- TEMP to avoid error missing data -->
            @php
                $dataDau['days'] = array_slice($dataDau['days'], 1, -1);
            @endphp
         
            @foreach ($dataDau['days'] as $day)
                @foreach (array_keys($dataDau['events']) as $event)
                    <script>
                         if ("{{$event}}" == "check") {
                            xValuesCheck.push("{{$day}}");
                            yValuesCheck.push("{{$dataTotal['events'][$event][$day]}}");
                        } else if ("{{$event}}" == "popover_open") {
                            xValuesPopoverOpen.push("{{$day}}");
                            yValuesPopoverOpen.push("{{$dataTotal['events'][$event][$day]}}");
                        } else if ("{{$event}}" == "ignore") {
                            xValuesIgnore.push("{{$day}}");
                            yValuesIgnore.push("{{$dataTotal['events'][$event][$day]}}");
                        } else if ("{{$event}}" == "alternative") {
                            xValuesAlternative.push("{{$day}}");
                            yValuesAlternative.push("{{$dataTotal['events'][$event][$day]}}");
                        }
                    
                    </script>
                @endforeach
            @endforeach


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
            const formattedDate = xValuesCheck.map(function(x) {
                year = x.split("-")[0];
                month = x.split("-")[1];
                day = x.split("-")[2];
                        return day + "." + month;
            });

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

            const changeInCheckPercentage = (((totalWeeklyCheck - totalWeeklyCheckPrevious) / totalWeeklyCheckPrevious) * 100).toFixed(2);
            const changeInPopoverPercentage = (((totalWeeklyPopoverOpen - totalWeeklyPopoverOpenPrevious) / totalWeeklyPopoverOpenPrevious) * 100).toFixed(2);
            const changeInIgnorePercentage = (((totalWeeklyIgnore - totalWeeklyIgnorePrevious) / totalWeeklyIgnorePrevious) * 100).toFixed(2);
            const changeInAlternativePercentage = (((totalWeeklyAlternative - totalWeeklyAlternativePrevious) / totalWeeklyAlternativePrevious) * 100).toFixed(2);

            let checkDaysInRow = 0;
            for (let i = yValuesCheck.length - 1; i >= 0; i--) {
                if (yValuesCheck[i] != '0') {
                    checkDaysInRow++;
                } else {
                    break;
                }
            }            
</script>

<x-app-layout>
    <div class="wittyworks-navigation-wrapper">@livewire('navigation-menu')</div>
        <div class="wittyworks-page-wrapper">
            <div class="wittyworks-page lg:ml-20">
        <body>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>

            <div class="ibarra-sub-title-h2">Activity</div>
            <div class="wittyworks-form-section container border-radius">
                <div class="container-row wittyworks-margin-top" style="width: 100%;">
                    <div class="container-column" style="margin-left: 2em;">
                        <div class="container-row" style="align-items: center">
                            <div id="checkDaysInRow" class="ibarra-sub-title-h2-purple"></div>
                            <div class="lato-small-text-p">&nbsp; Days Witty writing streak</div>
                        </div>

                        <div class="container-row  wittyworks-margin-top" >
                            <div id="changeInCheckPercentage" class="lato-small-paragraph-title-h4-purple"></div>
                            <div class="lato-small-text-p">Check requests (week)</div>
                        </div>

                        <div class="container-row" >
                            <div id="changeInPopoverPercentage" class="lato-small-paragraph-title-h4-purple"></div>
                            <div class="lato-small-text-p">Popover Open (week)</div>
                        </div>

                        <div class="container-row" >
                            <div id="changeInAlternativePercentage" class="lato-small-paragraph-title-h4-purple"></div>
                            <div class="lato-small-text-p">Alternative clicked (week)</div>
                        </div>

                        <div class="container-row" >
                            <div id="changeInIgnorePercentage" class="lato-small-paragraph-title-h4-purple"></div>
                            <div class="lato-small-text-p margin-bottom">Ignored words (week)</div>
                        </div>
                    </div>

                    <canvas id="requestRatiosChartDoughnut" class="wittyworks-analytics-chart-medium" style="margin-left: auto; margin-right: -5em"></canvas>
                </div>

                <canvas id="eventsChart" class="wittyworks-analytics-chart-extra-large"></canvas>
                <div class="container-row wittyworks-margin-top">
                    <canvas id="eventsCheckChart" class="wittyworks-analytics-chart-small wittyworks-margin-right"></canvas>
                    <canvas id="eventsPopoverChart" class="wittyworks-analytics-chart-small wittyworks-margin-right"></canvas>
                    <canvas id="eventsIgnoreChart" class="wittyworks-analytics-chart-small wittyworks-margin-right"></canvas>
                     <canvas id="eventsAlternativeChart" class="wittyworks-analytics-chart-small"></canvas>
                </div>
            </div>

            <div class="ibarra-sub-title-h2 wittyworks-margin-top">Top Categories</div>
            <div class="wittyworks-form-section container border-radius">
                <div class="container-row wittyworks-margin-top">
                    <canvas id="categoriesRadar" class="wittyworks-analytics-chart-medium-radar" style="margin-left: -5em"></canvas>
                    <div class="container-column">
                            <canvas id="topSubCategoriesChartDoughnut" class="wittyworks-analytics-chart-medium"></canvas>
                            <canvas id="topSubCategoriesChartDoughnutWeek" class="wittyworks-analytics-chart-medium"></canvas>
                    </div>

                    <canvas id="topSubCategoriesChart" class="wittyworks-analytics-chart-extra-large wittyworks-margin-top"></canvas>
                </div>
            </div>
            
            <div class="ibarra-sub-title-h2  wittyworks-margin-top">Top words</div>
            <div class="wittyworks-form-section container border-radius">
                <div class="container-row wittyworks-margin-top">
                    <canvas id="wordsRadar" class="wittyworks-analytics-chart-medium-radar" style="margin-left: -5em"></canvas>
                    <div class="container-column">
                            <canvas id="topWordsChartDoughnut" class="wittyworks-analytics-chart-medium"></canvas>
                            <canvas id="topWordsChartDoughnutWeek" class="wittyworks-analytics-chart-medium"></canvas>
                    </div>

                    <canvas id="topWordsChart" class="wittyworks-analytics-chart-extra-large wittyworks-margin-top"></canvas>
                </div>
            </div>

           <!-- CHARTS -->
            <script>
                Chart.defaults.global.defaultFontFamily = 'Lato';
                Chart.defaults.global.defaultFontColor = '#000000';

                const xValuesTopSubCategoriesCut = xValuesTopSubCategories.slice(0, 15);
                const yValuesTopSubCategoriesCut = yValuesTopSubCategories.slice(0, 15);
                new Chart("topSubCategoriesChart", {
                type: "bar",
                data: {
                    labels: xValuesTopSubCategoriesCut,
                    datasets: [{
                    backgroundColor: colors,
                    data: yValuesTopSubCategoriesCut
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top Subcategories (all time)",
                    fontSize: 16,
                    fontStyle: 'normal',
                    }
                }
                });

                 // TOP SUBCATEGORIES DOUGHNUT CHART 
                const xValuesTopSubCategoriesCutDoughnut = xValuesTopSubCategories.slice(0, 5);
                const yValuesTopSubCategoriesCutDoughnut = yValuesTopSubCategories.slice(0, 5);
       
                new Chart("topSubCategoriesChartDoughnut", {
                type: "doughnut",
                data: {
                    labels: xValuesTopSubCategoriesCutDoughnut,
                    datasets: [{
                    backgroundColor: [colors[6], colors[8], colors[10],colors[12], colors[14]],
                    data: yValuesTopSubCategoriesCutDoughnut
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top 5 Subcategories (all time)",
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
                });
                //TODO -> get correct data
                 // TOP SUBCATEGORIES DOUGHNUT CHART (week)
                 const xValuesTopSubCategoriesCutDoughnutWeek = xValuesTopSubCategories.slice(0, 5);
                const yValuesTopSubCategoriesCutDoughnutWeek = yValuesTopSubCategories.slice(0, 5);
       
                new Chart("topSubCategoriesChartDoughnutWeek", {
                type: "doughnut",
                data: {
                    labels: xValuesTopSubCategoriesCutDoughnutWeek,
                    datasets: [{
                    backgroundColor: [colors[6], colors[8], colors[10],colors[12], colors[14]],
                    data: yValuesTopSubCategoriesCutDoughnutWeek
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top 5 Subcategories (week)",
                    fontSize: 16,
                    fontStyle: 'normal'
                    }
                }
                });
                
                
            // <!-- TODO: radar how the categories changed over time -->
                const data = { 
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
                    data: data,
                    options: {
                        elements: {
                        line: {
                            borderWidth: 3
                        }
                        }
                    },
                    });

            // TOP WORDS BAR CHART
            const xValuesTopWordsCut = xValuesTopWords.slice(0, 15);
            const yValuesTopWordsCut = yValuesTopWords.slice(0, 15);
            new Chart("topWordsChart", {
            type: "bar",
            data: {
                labels: xValuesTopWordsCut,
                datasets: [{
                backgroundColor: colors,
                data: yValuesTopWordsCut
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top Words",
                fontSize: 16,
                fontStyle: 'normal'
                }
            }
            });

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
            

            // TOP WORDS DOUGHNUT CHART 
            const xValuesTopWordsCutDoughnut = xValuesTopWords.slice(0, 5);
            const yValuesTopWordsCutDoughnut = yValuesTopWords.slice(0, 5);
            new Chart("topWordsChartDoughnut", {
            type: "doughnut",
            data: {
                labels: xValuesTopWordsCutDoughnut,
                datasets: [{
                backgroundColor: [colors[3], colors[6], colors[9],colors[12], colors[14]],
                data: yValuesTopWordsCutDoughnut
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top 5 Words",
                fontSize: 16,
                fontStyle: 'normal'
                }
            }
            });

            //TODO -> get correct data
            // TOP WORDS DOUGHNUT CHART WEEK
            const xValuesTopWordsCutDoughnutWeek = xValuesTopWords.slice(0, 5);
            const yValuesTopWordsCutDoughnutWeek = yValuesTopWords.slice(0, 5);
            new Chart("topWordsChartDoughnutWeek", {
            type: "doughnut",
            data: {
                labels: xValuesTopWordsCutDoughnutWeek,
                datasets: [{
                backgroundColor: [colors[3], colors[6], colors[9],colors[12], colors[14]],
                data: yValuesTopWordsCutDoughnutWeek
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top 5 Words",
                fontSize: 16,
                fontStyle: 'normal'
                }
            }
            });

            //EVENTS CHART
            new Chart("eventsChart", {
            type: "line",
            data: {
                labels: formattedDate,
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
                }, 
                { 
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
                },
                scales: {
                    maxRotation: 0,

                   
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
                },
            ]
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
                },   
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
                },
            ]
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
                },   
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
                },   
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
                },
            ]
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


        document.getElementById("changeInCheckPercentage").innerHTML = changeInCheckPercentage >= 0 ? `+${changeInCheckPercentage}%` : `${changeInCheckPercentage}% &nbsp;`;
        document.getElementById("changeInPopoverPercentage").innerHTML = changeInPopoverPercentage >= 0 ? `+${changeInPopoverPercentage}%` : `${changeInPopoverPercentage}% &nbsp;`;
        document.getElementById("changeInIgnorePercentage").innerHTML = changeInIgnorePercentage >= 0 ? `+${changeInIgnorePercentage}%` : `${changeInIgnorePercentage}% &nbsp;`;
        document.getElementById("changeInAlternativePercentage").innerHTML = changeInAlternativePercentage >= 0 ? `+${changeInAlternativePercentage}%` : `${changeInAlternativePercentage}% &nbsp;`;
        document.getElementById("checkDaysInRow").innerHTML = checkDaysInRow;

        
        </script>
    </div>
        </div>
    </div>
</x-app-layout>