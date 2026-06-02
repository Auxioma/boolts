// assets/controllers/mes_biens/adresse_autocomplete_controller.js

import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'adresse',
        'codePostal',
        'ville',
        'pays',
        'latitude',
        'longitude',
    ];

    connect() {
        this.timeout = null;
        this.abortController = null;
        this.activeInput = null;

        this.resultsElement = document.createElement('div');
        this.resultsElement.className = 'list-group position-absolute w-100 shadow';
        this.resultsElement.style.zIndex = '1000';
        this.resultsElement.style.maxHeight = '300px';
        this.resultsElement.style.overflowY = 'auto';
        this.resultsElement.style.display = 'none';

        this.closeOnOutsideClick = this.closeOnOutsideClick.bind(this);

        document.addEventListener('click', this.closeOnOutsideClick);
    }

    disconnect() {
        document.removeEventListener('click', this.closeOnOutsideClick);

        if (this.abortController) {
            this.abortController.abort();
        }

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        this.resultsElement.remove();
    }

    search(event) {
        this.activeInput = event.currentTarget;

        clearTimeout(this.timeout);

        const query = this.buildQuery();

        if (query.length < 3) {
            this.hideResults();
            return;
        }

        this.timeout = setTimeout(() => {
            this.fetchAddresses(query);
        }, 300);
    }

    buildQuery() {
        const adresse = this.hasAdresseTarget ? this.adresseTarget.value.trim() : '';
        const codePostal = this.hasCodePostalTarget ? this.codePostalTarget.value.trim() : '';
        const ville = this.hasVilleTarget ? this.villeTarget.value.trim() : '';

        if (this.hasAdresseTarget && this.activeInput === this.adresseTarget) {
            return adresse;
        }

        if (this.hasCodePostalTarget && this.activeInput === this.codePostalTarget) {
            return [codePostal, ville].filter(Boolean).join(' ');
        }

        if (this.hasVilleTarget && this.activeInput === this.villeTarget) {
            return [ville, codePostal].filter(Boolean).join(' ');
        }

        return [adresse, codePostal, ville].filter(Boolean).join(' ');
    }

    async fetchAddresses(query) {
        try {
            if (this.abortController) {
                this.abortController.abort();
            }

            this.abortController = new AbortController();

            const url = `https://photon.komoot.io/api/?q=${encodeURIComponent(query)}&limit=5&lang=fr`;

            const response = await fetch(url, {
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                this.hideResults();
                return;
            }

            const data = await response.json();

            if (!data.features || data.features.length === 0) {
                this.hideResults();
                return;
            }

            this.renderResults(data.features);
        } catch (error) {
            if (error.name !== 'AbortError') {
                console.error('Erreur autocomplete adresse :', error);
            }
        }
    }

    renderResults(features) {
        this.resultsElement.innerHTML = '';

        const parent = this.activeInput.closest('.js-address-autocomplete-field');

        if (!parent) {
            return;
        }

        parent.classList.add('position-relative');
        parent.appendChild(this.resultsElement);

        features.forEach((feature) => {
            const props = feature.properties || {};

            const rue = this.buildStreet(props);
            const ville = this.getCity(props);
            const codePostal = props.postcode || '';
            const pays = props.country || '';

            const label = [
                rue || props.name,
                codePostal,
                ville,
                pays,
            ]
                .filter(Boolean)
                .join(', ');

            if (!label) {
                return;
            }

            const item = document.createElement('button');

            item.type = 'button';
            item.className = 'list-group-item list-group-item-action text-start';
            item.innerText = label;

            item.addEventListener('click', () => {
                this.selectAddress(feature);
            });

            this.resultsElement.appendChild(item);
        });

        if (this.resultsElement.children.length === 0) {
            this.hideResults();
            return;
        }

        this.resultsElement.style.display = 'block';
    }

    selectAddress(feature) {
        const props = feature.properties || {};

        const rue = this.buildStreet(props);
        const ville = this.getCity(props);
        const codePostal = props.postcode || '';
        const pays = props.country || '';
        const countryCode = props.countrycode || '';

        const coordinates = feature.geometry && feature.geometry.coordinates
            ? feature.geometry.coordinates
            : [];

        const longitude = coordinates[0] ?? '';
        const latitude = coordinates[1] ?? '';

        if (this.hasAdresseTarget && rue) {
            this.adresseTarget.value = rue;
            this.dispatchNativeChange(this.adresseTarget);
        }

        if (this.hasCodePostalTarget && codePostal) {
            this.codePostalTarget.value = codePostal;
            this.dispatchNativeChange(this.codePostalTarget);
        }

        if (this.hasVilleTarget && ville) {
            this.villeTarget.value = ville;
            this.dispatchNativeChange(this.villeTarget);
        }

        if (this.hasPaysTarget) {
            this.setPaysValue(pays, countryCode);
        }

        if (this.hasLatitudeTarget) {
            this.latitudeTarget.value = latitude;
            this.dispatchNativeChange(this.latitudeTarget);
        }

        if (this.hasLongitudeTarget) {
            this.longitudeTarget.value = longitude;
            this.dispatchNativeChange(this.longitudeTarget);
        }

        this.hideResults();
    }

    buildStreet(props) {
        const houseNumber = props.housenumber || '';
        const street = props.street || props.name || '';

        return [
            houseNumber,
            street,
        ]
            .filter(Boolean)
            .join(' ');
    }

    getCity(props) {
        return props.city
            || props.town
            || props.village
            || props.municipality
            || props.county
            || '';
    }

    setPaysValue(pays, countryCode) {
        const field = this.paysTarget;

        if (!pays && !countryCode) {
            return;
        }

        if (field.tagName === 'SELECT') {
            const normalizedPays = this.normalize(pays);
            const normalizedCountryCode = this.normalize(countryCode);

            const option = Array.from(field.options).find((option) => {
                const optionText = this.normalize(option.textContent);
                const optionValue = this.normalize(option.value);

                return optionText === normalizedPays
                    || optionText.includes(normalizedPays)
                    || optionValue === normalizedPays
                    || optionValue === normalizedCountryCode;
            });

            if (option) {
                field.value = option.value;
                this.dispatchNativeChange(field);
            }

            return;
        }

        field.value = pays || countryCode;
        this.dispatchNativeChange(field);
    }

    normalize(value) {
        return String(value || '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    dispatchNativeChange(element) {
        element.dispatchEvent(new Event('change', {
            bubbles: true,
        }));
    }

    hideResults() {
        this.resultsElement.innerHTML = '';
        this.resultsElement.style.display = 'none';
    }

    closeOnOutsideClick(event) {
        const isInsideResults = this.resultsElement.contains(event.target);

        const isInsideField = this.element.contains(event.target);

        if (isInsideResults || isInsideField) {
            return;
        }

        this.hideResults();
    }
}
