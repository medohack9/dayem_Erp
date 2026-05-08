document.addEventListener('DOMContentLoaded', function() {
    const tasksCanvas = document.getElementById('tasksChart');
    const ticketsCanvas = document.getElementById('ticketsChart');
    
    if (!tasksCanvas && !ticketsCanvas) {
        return;
    }

    const chartColors = {
        created: '#F4C400',
        completed: '#10B981',
        closed: '#6B7280',
        grid: '#E5E7EB',
        text: '#374151',
    };

    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                align: 'end',
                rtl: true,
                labels: {
                    font: {
                        family: 'Cairo',
                        size: 12,
                    },
                    usePointStyle: true,
                    pointStyle: 'circle',
                    padding: 15,
                },
            },
            tooltip: {
                rtl: true,
                titleFont: {
                    family: 'Cairo',
                },
                bodyFont: {
                    family: 'Cairo',
                },
            },
        },
        scales: {
            x: {
                stacked: false,
                grid: {
                    display: false,
                },
                ticks: {
                    font: {
                        family: 'Cairo',
                        size: 11,
                    },
                    color: chartColors.text,
                },
            },
            y: {
                stacked: false,
                beginAtZero: true,
                grid: {
                    color: chartColors.grid,
                },
                ticks: {
                    font: {
                        family: 'Cairo',
                        size: 11,
                    },
                    color: chartColors.text,
                    stepSize: 1,
                },
            },
        },
    };

    function initTasksChart() {
        if (!tasksCanvas) return;

        fetch(baseUrl + '/api/dashboard/tasks-chart', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Failed to load tasks chart data');
                return;
            }

            const data = result.data;
            const hasData = data.created.some(v => v > 0) || data.completed.some(v => v > 0);

            if (!hasData) {
                const container = tasksCanvas.parentElement;
                container.innerHTML = '<div class="flex items-center justify-center h-64 text-gray-500">لا توجد بيانات</div>';
                return;
            }

            new Chart(tasksCanvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'مهام جديدة',
                            data: data.created,
                            backgroundColor: chartColors.created,
                            borderRadius: 4,
                            barThickness: 20,
                        },
                        {
                            label: 'مهام مكتملة',
                            data: data.completed,
                            backgroundColor: chartColors.completed,
                            borderRadius: 4,
                            barThickness: 20,
                        },
                    ],
                },
                options: chartOptions,
            });
        })
        .catch(error => {
            console.error('Error loading tasks chart:', error);
        });
    }

    function initTicketsChart() {
        if (!ticketsCanvas) return;

        fetch(baseUrl + '/api/dashboard/tickets-chart', {
            method: 'GET',
            headers: {
                'Accept': 'application/json',
            },
        })
        .then(response => response.json())
        .then(result => {
            if (!result.success) {
                console.error('Failed to load tickets chart data');
                return;
            }

            const data = result.data;
            const hasData = data.created.some(v => v > 0) || data.closed.some(v => v > 0);

            if (!hasData) {
                const container = ticketsCanvas.parentElement;
                container.innerHTML = '<div class="flex items-center justify-center h-64 text-gray-500">لا توجد بيانات</div>';
                return;
            }

            new Chart(ticketsCanvas, {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [
                        {
                            label: 'تذاكر جديدة',
                            data: data.created,
                            backgroundColor: chartColors.created,
                            borderRadius: 4,
                            barThickness: 20,
                        },
                        {
                            label: 'تذاكر مغلقة',
                            data: data.closed,
                            backgroundColor: chartColors.closed,
                            borderRadius: 4,
                            barThickness: 20,
                        },
                    ],
                },
                options: chartOptions,
            });
        })
        .catch(error => {
            console.error('Error loading tickets chart:', error);
        });
    }

    initTasksChart();
    initTicketsChart();
});