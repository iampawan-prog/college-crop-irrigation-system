var dataEl = document.getElementById('chart-data');
var canvas = document.getElementById('irrigationChart');
var data = { labels: [], counts: [] };

if (dataEl && dataEl.textContent) {
    try {
        data = JSON.parse(dataEl.textContent);
    } catch (err) {
        data = { labels: [], counts: [] };
    }
}

if (canvas && window.Chart && data.labels && data.labels.length) {
    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Irrigations',
                    data: data.counts,
                    backgroundColor: '#198754'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });
}
