jQuery(document).ready(function ($) {
    // بارگذاری داده‌های نمودار
    $.post(feedbackAjax.ajax_url, { 
        action: 'fetch_chart_data', 
        _ajax_nonce: feedbackAjax.nonce 
    }, function (response) {
        if (response.success) {
            const labels = response.data.map(entry => entry.date);
            const data = response.data.map(entry => parseFloat(entry.avg_rating));

            const ctx = $('#feedback-chart');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Rating',
                        data: data,
                        borderColor: 'rgba(75, 192, 192, 1)',
                        backgroundColor: 'rgba(75, 192, 192, 0.2)',
                        fill: true,
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Average Rating'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Date'
                            }
                        }
                    }
                }
            });
        } else {
            console.error(response.data.message || 'Error fetching chart data.');
        }
    });
});
