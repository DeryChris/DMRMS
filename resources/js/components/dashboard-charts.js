import Chart from 'chart.js/auto';

export function initRegionalChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    const labels = data.map(d => d.region);
    const values = data.map(d => d.count);

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: 'Applicants by Region',
                data: values,
                backgroundColor: [
                    '#6366f1', '#06b6d4', '#f43f5e', '#22c55e',
                    '#f97316', '#a855f7', '#ec4899', '#eab308',
                    '#14b8a6', '#3b82f6',
                ],
                borderWidth: 0,
                borderRadius: 4,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: { family: 'Inter, sans-serif' },
                    bodyFont: { family: 'Inter, sans-serif' },
                    callbacks: {
                        label: (ctx) => `${ctx.parsed.y.toLocaleString()} applicants`,
                    },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { family: 'Inter, sans-serif' } },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter, sans-serif', size: 11 } },
                },
            },
        },
    });
}

export function initGenderChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.map(d => d.gender),
            datasets: [{
                data: data.map(d => d.count),
                backgroundColor: ['#6366f1', '#ec4899'],
                borderWidth: 0,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Inter, sans-serif', size: 12 },
                        padding: 16,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    callbacks: {
                        label: (ctx) => {
                            const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                            const pct = ((ctx.parsed / total) * 100).toFixed(1);
                            return `${ctx.label}: ${ctx.parsed.toLocaleString()} (${pct}%)`;
                        },
                    },
                },
            },
        },
    });
}

export function initFunnelChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    const stages = data.map(d => d.stage);
    const counts = data.map(d => d.count);
    const maxCount = Math.max(...counts);

    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: stages,
            datasets: [{
                label: 'Candidates',
                data: counts,
                backgroundColor: counts.map((c, i) => {
                    const alpha = c / maxCount;
                    return `rgba(99, 102, 241, ${alpha})`;
                }),
                borderWidth: 0,
                borderRadius: 4,
                barPercentage: 0.6,
            }],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#1f2937',
                    callbacks: {
                        label: (ctx) => {
                            const pct = ((ctx.parsed.x / counts[0]) * 100).toFixed(1);
                            return `${ctx.parsed.x.toLocaleString()} (${pct}% of initial)`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { family: 'Inter, sans-serif' } },
                },
                y: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter, sans-serif', size: 11 } },
                },
            },
        },
    });
}

export function initTrendChart(canvasId, data) {
    const ctx = document.getElementById(canvasId);
    if (!ctx) return;

    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.map(d => d.date),
            datasets: [
                {
                    label: 'Applications',
                    data: data.map(d => d.applications),
                    borderColor: '#6366f1',
                    backgroundColor: 'rgba(99, 102, 241, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#6366f1',
                },
                {
                    label: 'Approved',
                    data: data.map(d => d.approved),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.4,
                    pointRadius: 3,
                    pointBackgroundColor: '#52B788',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: { family: 'Inter, sans-serif', size: 12 },
                        padding: 16,
                        usePointStyle: true,
                    },
                },
                tooltip: {
                    backgroundColor: '#1f2937',
                    titleFont: { family: 'Inter, sans-serif' },
                    bodyFont: { family: 'Inter, sans-serif' },
                },
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { family: 'Inter, sans-serif' } },
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'Inter, sans-serif', size: 11 } },
                },
            },
        },
    });
}
