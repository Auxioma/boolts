import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['option', 'price', 'period', 'subscriptionLink'];

    select(event) {
        const selectedPeriod = event.currentTarget.dataset.period;

        this.optionTargets.forEach((option) => {
            const isActive = option.dataset.period === selectedPeriod;

            option.classList.toggle('achat-active', isActive);
            option.setAttribute('aria-pressed', isActive ? 'true' : 'false');
        });

        this.priceTargets.forEach((price) => {
            const newPrice = price.dataset[selectedPeriod];

            if (newPrice?.trim()) {
                price.textContent = newPrice;
            }
        });

        this.periodTargets.forEach((period) => {
            period.textContent = selectedPeriod === 'annual' ? '/an' : '/mois';
        });

        this.subscriptionLinkTargets.forEach((link) => {
            const newUrl = selectedPeriod === 'annual'
                ? link.dataset.annualUrl
                : link.dataset.monthlyUrl;
            const isAvailable = selectedPeriod === 'annual'
                ? link.dataset.annualAvailable === '1'
                : link.dataset.monthlyAvailable === '1';

            link.classList.toggle('d-none', !isAvailable);

            if (newUrl && isAvailable) {
                link.href = newUrl;
            }
        });
    }
}
