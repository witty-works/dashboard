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

    const colors = [
        "#070612",
        "#161137",
        "#1d1649",
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
        "#bfb8e9"
    ];
</script>
<x-guest-layout>
    <div>
        <x-jet-authentication-card-logo />
    </div>
    <div class="wittyworks-analytics-page-wrapper">
        <body>
            <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.js"></script>
            <div class="ibarra-sub-title-h2">Witty Writing Streak</div>
            <div class="lato-small-paragraph-title-h4">Some text about why we are showing this and why its interesting</div>


            <div class="ibarra-sub-title-h2">Top Categories</div>
            <div class="lato-small-paragraph-title-h4">Some text about why we are showing this and why its interesting</div>
            <div class="container-row">
                <canvas id="topSubCategoriesChart" class="wittyworks-analytics-chart-medium"></canvas>
                <canvas id="topSubCategoriesChartDoughnut" class="wittyworks-analytics-chart-medium"></canvas>
                <canvas id="categoriesRadar" class="wittyworks-analytics-chart-large"></canvas>
            </div>
            

            <div class="container-row">
                <div class="ibarra-sub-title-h2">Top words</div>
                <div class="lato-small-paragraph-title-h4">Some text about why we are showing this and why its interesting</div>
                <canvas id="topWordsChart" class="wittyworks-analytics-chart-medium"></canvas>
                <canvas id="topWordsChartDoughnut" class="wittyworks-analytics-chart-small"></canvas>
            </div>
            <canvas id="eventsChart" class="wittyworks-analytics-chart-large"></canvas>
            <div class="container-row">
                <canvas id="eventsCheckChart" class="wittyworks-analytics-chart-medium"></canvas>
                <canvas id="eventsPopoverChart" class="wittyworks-analytics-chart-medium"></canvas>
            </div>
            <div class="container-row">
                <canvas id="eventsIgnoreChart" class="wittyworks-analytics-chart-medium"></canvas>
                <canvas id="eventsAlternativeChart" class="wittyworks-analytics-chart-medium"></canvas>
            </div>

            
            
           

            @php
                $currentURL = URL::current();
                $currentURL.= strpos('?', $currentURL) === false ? '?' : '&';
            @endphp

            <a href="{{ $currentURL }}refresh=true">Refresh Charts</a>
          
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

   

            <!-- TOP SUBCATEGORIES BAR CHART -->
            <script>
                xValuesTopSubCategories.length = 15;
                yValuesTopSubCategories.length = 15;
                new Chart("topSubCategoriesChart", {
                type: "bar",
                data: {
                    labels: xValuesTopSubCategories,
                    datasets: [{
                    backgroundColor: colors,
                    data: yValuesTopSubCategories
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top Subcategories"
                    }
                }
                });
            </script>

            <!-- TOP SUBCATEGORIES DOUGHNUT CHART -->
            <script>
                xValuesTopSubCategories.length = 5;
                yValuesTopSubCategories.length = 5;
                new Chart("topSubCategoriesChartDoughnut", {
                type: "doughnut",
                data: {
                    labels: xValuesTopSubCategories,
                    datasets: [{
                    backgroundColor: colors,
                    data: yValuesTopSubCategories
                    }]
                },
                options: {
                    legend: {display: false},
                    title: {
                    display: true,
                    text: "Top 5 Subcategories"
                    }
                }
                });
            </script>

            <!-- TODO: radar how the categories changed over time -->
            <script>
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
                    backgroundColor: colors[4],
                    borderColor: colors[1],
                }, {
                    label: 'Categories This Week',
                    data: [28, 48, 40, 19, 96, 27, 100],
                    fill: true,
                    backgroundColor: colors[3],
                    borderColor: colors[2],
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
            </script>

        <!-- TOP WORDS BAR CHART -->
        <script>
            xValuesTopWords.length = 15;
            yValuesTopWords.length = 15;
            new Chart("topWordsChart", {
            type: "bar",
            data: {
                labels: xValuesTopWords,
                datasets: [{
                backgroundColor: colors,
                data: yValuesTopWords
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top Words"
                }
            }
            });
        </script>

        <!-- TOP WORDS DOUGHNUT CHART -->
        <script>
            xValuesTopWords.length = 5;
            yValuesTopWords.length = 5;
            new Chart("topWordsChartDoughnut", {
            type: "doughnut",
            data: {
                labels: xValuesTopWords,
                datasets: [{
                backgroundColor: colors,
                data: yValuesTopWords
                }]
            },
            options: {
                legend: {display: false},
                title: {
                display: true,
                text: "Top 5 Words"
                }
            }
            });
        </script>

        <!-- EVENTS CHART -->
        <script>
            new Chart("eventsChart", {
            type: "line",
            data: {
                labels: xValuesCheck,
                datasets: [{ 
                data: yValuesCheck,
                borderColor: colors[0],
                fill: false,
                label: 'Check',
                }, { 
                data: yValuesPopoverOpen,
                borderColor: colors[1],
                fill: false,
                label: 'Popover Open',
                }, { 
                data: yValuesIgnore,
                borderColor: colors[2],
                fill: false,
                label: 'Ignore',
                }, 
                { 
                data: yValuesAlternative,
                borderColor: colors[3],
                fill: false,
                label: 'Alternative',
                }]
            },
            options: {
                title: {
                display: true,
                text: 'Events'
                },
            }   
        });
        </script>
        <script>
            new Chart("eventsCheckChart", {
            type: "line",
            data: {
                labels: xValuesCheck,
                datasets: [{ 
                data: yValuesCheck,
                borderColor: colors[0],
                fill: false,
                label: 'Check',
                },
            ]
            },
            options: {
                title: {
                display: true,
                text: 'Events'
                },
            }   
        });
        </script>
        <script>
            new Chart("eventsPopoverChart", {
            type: "line",
            data: {
                labels: xValuesCheck,
                datasets: [{ 
                data: yValuesPopoverOpen,
                borderColor: colors[1],
                fill: false,
                label: 'Popover Open',
                },
            ]
            },
            options: {
                title: {
                display: true,
                text: 'Events'
                },
            }   
        });
        </script>
        <script>
            new Chart("eventsIgnoreChart", {
            type: "line",
            data: {
                labels: xValuesCheck,
                datasets: [{ 
                data: yValuesIgnore,
                borderColor: colors[2],
                fill: false,
                label: 'Ignore',
                },
            ]
            },
            options: {
                title: {
                display: true,
                text: 'Events'
                },
            }   
        });
        </script>
        <script>
            new Chart("eventsAlternativeChart", {
            type: "line",
            data: {
                labels: xValuesCheck,
                datasets: [{ 
                data: yValuesAlternative,
                borderColor: colors[3],
                fill: false,
                label: 'Alternative',
                }]
            },
            options: {
                title: {
                display: true,
                text: 'Events'
                },
            }   
        });
        </script>
    </div>
</x-guest-layout>