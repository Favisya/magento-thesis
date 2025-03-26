define([
    'jquery'
], function ($) {
    'use strict';
    
    return function(config) {
        let chartData = config.chartData;
        let chart = null;
        
        function renderChart() {
            if (chart) {
                chart.destroy();
            }
            
            const ctx = document.getElementById('logErrorChart').getContext('2d');
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: chartData.datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: $.mage.__('Log Errors by Date and Severity')
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false
                        },
                        legend: {
                            position: 'top'
                        }
                    },
                    scales: {
                        x: {
                            title: {
                                display: true,
                                text: $.mage.__('Date')
                            }
                        },
                        y: {
                            title: {
                                display: true,
                                text: $.mage.__('Count')
                            },
                            beginAtZero: true
                        }
                    }
                }
            });
        }
        
        $(function() {
            if (chartData && chartData.labels && chartData.labels.length) {
                renderChart();
            } else {
                $('#logErrorChart').after('<div class="message message-notice">' + $.mage.__('No data available for the selected period') + '</div>');
            }
            
            $('#apply-filters').on('click', function(e) {
                e.preventDefault();
                let fromDate = $('#date_from').val();
                let toDate = $('#date_to').val();
                let url = window.location.href.split('?')[0];
                let params = [];
                
                if (fromDate) {
                    params.push('from=' + fromDate);
                }
                
                if (toDate) {
                    params.push('to=' + toDate);
                }
                
                if (params.length) {
                    url += '?' + params.join('&');
                }
                
                window.location = url;
            });
        });
    };
}); 