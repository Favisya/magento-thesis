define([
    'jquery',
    'Chart'
], function ($, Chart) {
    'use strict';
    
    return function(config) {
        let chartData = config.chartData;
        let chart = null;
        
        function renderChart() {
            if (chart) {
                chart.destroy();
            }
            
            const ctx = document.getElementById('logErrorChart').getContext('2d');
            
            const datasets = Object.entries(chartData.datasets).map(([severity, data]) => ({
                label: severity.charAt(0).toUpperCase() + severity.slice(1),
                data: data,
                backgroundColor: getSeverityColor(severity),
                borderColor: getSeverityColor(severity),
                borderWidth: 1
            }));
            
            chart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: chartData.labels,
                    datasets: datasets
                },
                options: {
                    responsive: true,
                    plugins: {
                        title: {
                            display: true,
                            text: $.mage.__('Logs by Date and Severity')
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
                            stacked: true,
                            title: {
                                display: true,
                                text: $.mage.__('Date')
                            }
                        },
                        y: {
                            stacked: true,
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

        function getSeverityColor(severity) {
            const colors = {
                'emergency': '#FF0000',
                'alert': '#FF4500',
                'critical': '#FF6347',
                'error': '#FFA500',
                'warning': '#FFD700',
                'notice': '#87CEEB',
                'info': '#98FB98',
                'debug': '#DDA0DD'
            };
            return colors[severity.toLowerCase()] || '#808080';
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