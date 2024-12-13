document.addEventListener('DOMContentLoaded', function () {
    // بارگذاری داده‌های نمودار
    const formData = new FormData();
    formData.append('action', 'fetch_chart_data');
    formData.append('_ajax_nonce', feedbackAjax.nonce);

    fetch(feedbackAjax.ajax_url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const labels = data.data.map(entry => entry.date);
            const chartData = data.data.map(entry => parseFloat(entry.avg_rating));

            const ctx = document.getElementById('feedback-chart').getContext('2d');
            new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Average Rating',
                        data: chartData,
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
            console.error(data.data.message || 'Error fetching chart data.');
        }
    })
    .catch(error => console.error('Error:', error));

    // bulk delete
    jQuery(document).ready(function($) {
        // Select All checkbox functionality
        $('#select-all').on('change', function() {
            $('input[name="bulk_delete_ids[]"]').prop('checked', this.checked);
        });

        // Handle bulk delete form submission
        $('#bulk-delete-form').on('submit', function(e) {
            e.preventDefault(); // جلوگیری از ارسال خودکار فرم

            // Show confirmation prompt
            if (confirm('Are you sure you want to delete selected feedback?')) {
                // Manually submit the form if confirmed
                this.submit();
            }
        });
    });
});
