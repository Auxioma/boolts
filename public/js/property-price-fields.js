document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-property-price-transaction]').forEach((transactionField) => {
        const form = transactionField.closest('form');

        if (!form) {
            return;
        }

        const updatePriceFields = () => {
            const selectedOption = transactionField.options[transactionField.selectedIndex];
            const priceMode = selectedOption?.dataset.priceMode;

            form.querySelectorAll('.property-price-sale').forEach((field) => {
                field.hidden = 'sale' !== priceMode;
            });

            form.querySelectorAll('.property-price-rental').forEach((field) => {
                field.hidden = 'rental' !== priceMode;
            });
        };

        transactionField.addEventListener('change', updatePriceFields);
        updatePriceFields();
    });

    document.querySelectorAll('[data-property-energy-country]').forEach((countryField) => {
        const form = countryField.closest('form');
        const energyTab = form?.querySelector('#tab-energie');
        const energyTabLink = form?.querySelector('[href="#tab-energie"]');

        if (!form || !energyTab || !energyTabLink) {
            return;
        }

        const updateEnergyTab = () => {
            const country = countryField.value
                .trim()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase();
            const isFrance = 'france' === country || 'fr' === country;

            energyTab.hidden = !isFrance;
            energyTabLink.closest('.nav-item').hidden = !isFrance;

            if (!isFrance && energyTabLink.classList.contains('active')) {
                form.querySelector('.nav-item:not([hidden]) .nav-link')?.click();
            }
        };

        countryField.addEventListener('input', updateEnergyTab);
        countryField.addEventListener('change', updateEnergyTab);
        updateEnergyTab();
    });
});
