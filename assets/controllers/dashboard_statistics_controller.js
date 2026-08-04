import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['modal', 'start', 'end', 'profileViews', 'published', 'views', 'favorites', 'chart'];
    static values = { url: String };

    connect() {
        this.hiddenSeries = new Set();
    }

    async selectPeriod(event) {
        const period = event.currentTarget.dataset.period;
        if (period === 'custom') {
            return;
        }

        await this.load({ period });
    }

    async loadCustomPeriod() {
        if (!this.startTarget.value || !this.endTarget.value) {
            return;
        }

        await this.load({ period: 'custom', start: this.startTarget.value, end: this.endTarget.value });
        Modal.getOrCreateInstance(this.modalTarget).hide();
    }

    async load(parameters) {
        const url = new URL(this.urlValue, window.location.origin);
        Object.entries(parameters).forEach(([key, value]) => url.searchParams.set(key, value));

        const response = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!response.ok) {
            return;
        }

        const statistics = await response.json();
        this.profileViewsTarget.textContent = statistics.profileViews;
        this.publishedTarget.textContent = statistics.published;
        this.viewsTarget.textContent = statistics.views;
        this.favoritesTarget.textContent = statistics.favorites;

        const chartController = this.application.getControllerForElementAndIdentifier(
            this.chartTarget,
            'symfony--ux-chartjs--chart'
        );

        if (chartController?.chart) {
            chartController.chart.data = statistics.chart.data;
            chartController.chart.options = statistics.chart.options;
            this.hiddenSeries.forEach((index) => chartController.chart.setDatasetVisibility(index, false));
            chartController.chart.update();
        } else {
            this.chartTarget.dataset.symfonyUxChartjsChartViewValue = JSON.stringify(statistics.chart);
        }

        this.element.querySelectorAll('[data-period]').forEach((button) => {
            button.classList.toggle('active', button.dataset.period === parameters.period);
        });
        window.dispatchEvent(new CustomEvent('dashboard-period-changed', { detail: parameters }));
    }

    toggleSeries(event) {
        const card = event.currentTarget;
        const datasetIndex = Number(card.dataset.seriesIndex);
        const chartController = this.application.getControllerForElementAndIdentifier(
            this.chartTarget,
            'symfony--ux-chartjs--chart'
        );

        if (!chartController?.chart || Number.isNaN(datasetIndex)) {
            return;
        }

        const visible = chartController.chart.isDatasetVisible(datasetIndex);
        chartController.chart.setDatasetVisibility(datasetIndex, !visible);
        chartController.chart.update();

        if (visible) {
            this.hiddenSeries.add(datasetIndex);
        } else {
            this.hiddenSeries.delete(datasetIndex);
        }

        card.classList.toggle('is-inactive', visible);
        card.setAttribute('aria-pressed', visible ? 'false' : 'true');
    }
}
