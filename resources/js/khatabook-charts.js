import Chart from 'chart.js/auto';

const palette = ['#16a34a', '#2563eb', '#d97706', '#dc2626', '#7c3aed', '#0891b2'];

document.addEventListener('alpine:init', () => {
    window.Alpine.data('khatabookCharts', (initial) => ({
        charts: {},

        init() {
            this.update(initial);
        },

        update({ salesTrend, expenseBreakdown, topItems }) {
            this.line('salesTrend', salesTrend);
            this.pie('expenseBreakdown', expenseBreakdown);
            this.bar('topItems', topItems);
        },

        line(key, data) {
            const chart = this.charts[key];

            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.values;
                chart.update();

                return;
            }

            this.charts[key] = new Chart(this.$refs[key], {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{ label: 'Sales', data: data.values, borderColor: palette[0], backgroundColor: palette[0], tension: 0.3 }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        },

        pie(key, data) {
            const chart = this.charts[key];

            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.values;
                chart.update();

                return;
            }

            this.charts[key] = new Chart(this.$refs[key], {
                type: 'pie',
                data: {
                    labels: data.labels,
                    datasets: [{ data: data.values, backgroundColor: palette }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        },

        bar(key, data) {
            const chart = this.charts[key];

            if (chart) {
                chart.data.labels = data.labels;
                chart.data.datasets[0].data = data.values;
                chart.update();

                return;
            }

            this.charts[key] = new Chart(this.$refs[key], {
                type: 'bar',
                data: {
                    labels: data.labels,
                    datasets: [{ label: 'Revenue', data: data.values, backgroundColor: palette[1] }],
                },
                options: { responsive: true, maintainAspectRatio: false },
            });
        },
    }));
});
