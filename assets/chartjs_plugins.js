// Chart.js est chargé à la demande par le contrôleur ux-chartjs (lazy) :
// le plugin est enregistré quand il émet chartjs:init, avant le premier graphique.
document.addEventListener('chartjs:init', (event) => {
    event.detail.Chart.register({
        id: 'dashboardTooltipShadow',

        beforeTooltipDraw(chart) {
            if (!chart.canvas?.classList.contains('statistics-chart-canvas')) {
                return;
            }

            const { ctx } = chart;

            ctx.save();
            ctx.shadowColor = 'rgba(0, 0, 0, 0.20)';
            ctx.shadowBlur = 20;
            ctx.shadowOffsetX = 0;
            ctx.shadowOffsetY = 3;
        },

        afterTooltipDraw(chart) {
            if (!chart.canvas?.classList.contains('statistics-chart-canvas')) {
                return;
            }

            chart.ctx.restore();
        },
    });
});
