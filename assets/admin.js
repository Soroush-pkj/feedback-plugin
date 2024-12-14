document.addEventListener('DOMContentLoaded', function () {
    // Chart loading
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

    // Bulk delete
    
    const selectAll = document.getElementById('select-all');
    const feedbackCheckboxes = document.querySelectorAll('input[name="bulk_delete_ids[]"]');
    const bulkDeleteForm = document.getElementById('bulk-delete-form');

    
    selectAll.addEventListener('change', function () {
        feedbackCheckboxes.forEach(function (checkbox) {
            checkbox.checked = selectAll.checked;
        });
    });

    
    bulkDeleteForm.addEventListener('submit', function (e) {
        e.preventDefault();

        if (confirm('Are you sure you want to delete selected feedback?')) {
            this.submit();
        }
    });
});
