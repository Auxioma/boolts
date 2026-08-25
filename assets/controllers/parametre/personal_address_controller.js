import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.adresseInput = this.element.querySelector('[data-name="adresse"]');
        this.villeInput = this.element.querySelector('[data-name="ville"]');
        this.codePostalInput = this.element.querySelector('[data-name="codePostal"]');
        this.timeout = null;

        if (!this.adresseInput || !this.villeInput || !this.codePostalInput) {
            return;
        }

        this.results = document.createElement('div');
        this.results.className = 'list-group position-absolute w-75 shadow';
        this.results.style.zIndex = '1000';
        this.results.style.maxHeight = '300px';
        this.results.style.overflowY = 'auto';

        this.adresseInput.parentNode.style.position = 'relative';
        this.adresseInput.parentNode.appendChild(this.results);

        this.inputListener = () => {
            this.handleAddressInput();
        };

        this.outsideClickListener = (event) => {
            if (!this.results.contains(event.target) && event.target !== this.adresseInput) {
                this.clearResults();
            }
        };

        this.adresseInput.addEventListener('input', this.inputListener);
        document.addEventListener('click', this.outsideClickListener);
    }

    disconnect() {
        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        if (this.adresseInput && this.inputListener) {
            this.adresseInput.removeEventListener('input', this.inputListener);
        }

        if (this.outsideClickListener) {
            document.removeEventListener('click', this.outsideClickListener);
        }

        this.results?.remove();
    }

    handleAddressInput() {
        clearTimeout(this.timeout);

        const query = this.adresseInput.value.trim();

        if (query.length < 3) {
            this.clearResults();
            return;
        }

        this.timeout = setTimeout(() => {
            this.fetchSuggestions(query);
        }, 300);
    }

    async fetchSuggestions(query) {
        try {
            const response = await fetch(
                `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5&lang=fr`
            );

            const data = await response.json();
            this.clearResults();

            if (!data.features || data.features.length === 0) {
                return;
            }

            data.features.forEach((feature) => {
                this.results.appendChild(this.createSuggestion(feature));
            });
        } catch (error) {
            console.error('Erreur autocomplete adresse :', error);
        }
    }

    createSuggestion(feature) {
        const props = feature.properties;
        const rue = [
            props.housenumber,
            props.street || props.name
        ].filter(Boolean).join(' ');
        const label = [
            rue,
            props.postcode,
            props.city
        ].filter(Boolean).join(', ');
        const item = document.createElement('button');

        item.type = 'button';
        item.className = 'list-group-item list-group-item-action text-start';
        item.innerText = label;

        item.addEventListener('click', () => {
            this.adresseInput.value = rue;
            this.villeInput.value = props.city || '';
            this.codePostalInput.value = props.postcode || '';
            this.clearResults();
        });

        return item;
    }

    clearResults() {
        this.results.innerHTML = '';
    }
}
