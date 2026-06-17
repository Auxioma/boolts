import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'input',
        'results',

        'selectedValue',
        'selectedMapboxId',
        'selectedFeatureType',
        'selectedCountryName',
        'selectedCountryCode',
        'selectedRegionName',
        'selectedCityName',
        'selectedPostalCode',
        'selectedLatitude',
        'selectedLongitude',
        'selectedFullAddress',
        'selectedLocale',
        'selectedLocationJson',
    ];

    static values = {
        token: String,
        language: {
            type: String,
            default: 'fr',
        },
    };

    connect() {
        this.timeout = null;
        this.abortController = null;
        this.selectedLocation = null;

        this.handleClickOutside = this.handleClickOutside.bind(this);

        document.addEventListener('click', this.handleClickOutside);

        this.clearHiddenFields();
    }

    disconnect() {
        document.removeEventListener('click', this.handleClickOutside);

        if (this.abortController) {
            this.abortController.abort();
        }

        clearTimeout(this.timeout);
    }

    search() {
        clearTimeout(this.timeout);

        const query = this.inputTarget.value.trim();

        if (query.length < 2) {
            this.clearResults();
            return;
        }

        this.timeout = setTimeout(() => {
            this.fetchResults(query);
        }, 350);
    }

    async fetchResults(query) {
        if (!this.tokenValue) {
            console.error('Token Mapbox manquant.');
            return;
        }

        if (this.abortController) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();

        const params = new URLSearchParams({
            access_token: this.tokenValue,
            autocomplete: 'true',
            language: this.languageValue || 'fr',
            limit: '10',
            types: 'country,place,locality,postcode',
        });

        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${encodeURIComponent(query)}.json?${params.toString()}`;

        try {
            const response = await fetch(url, {
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                throw new Error(`Erreur Mapbox : ${response.status}`);
            }

            const data = await response.json();

            this.renderResults(data.features || []);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error(error);
            this.clearResults();
        }
    }

    renderResults(features) {
        this.resultsTarget.innerHTML = '';

        if (!features.length) {
            this.clearResults();
            return;
        }

        features.forEach((feature) => {
            const location = this.buildLocation(feature);

            if (!location.value) {
                return;
            }

            const item = document.createElement('li');

            item.classList.add('home-bt-search-bar__result');
            item.setAttribute('role', 'button');
            item.setAttribute('tabindex', '0');

            item.innerHTML = `
                <span class="home-bt-search-bar__result-title">
                    ${this.escapeHtml(location.value)}
                </span>

                <span class="home-bt-search-bar__result-subtitle">
                    ${this.escapeHtml(location.fullAddress)}
                </span>
            `;

            item.addEventListener('click', () => {
                this.selectLocation(location);
            });

            item.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    this.selectLocation(location);
                }
            });

            this.resultsTarget.appendChild(item);
        });

        this.resultsTarget.classList.add('is-active');
    }

    async selectLocation(location) {
        /*
         * Si l'utilisateur sélectionne une ville,
         * Mapbox ne donne pas toujours le code postal.
         *
         * Donc on tente une seconde requête reverse
         * depuis longitude/latitude pour récupérer un postcode.
         */
        if (
            !location.postalCode &&
            location.longitude &&
            location.latitude &&
            (location.featureType === 'place' || location.featureType === 'locality')
        ) {
            location.postalCode = await this.fetchPostcodeFromCoordinates(
                location.longitude,
                location.latitude
            );
        }

        this.selectedLocation = location;

        /*
         * Ce que l'utilisateur voit dans l'input.
         *
         * Ville sélectionnée      => Le Havre
         * Pays sélectionné       => France
         * Code postal sélectionné => 76600
         */
        this.inputTarget.value = location.value;

        /*
         * Ce qui est envoyé dans les champs cachés.
         */
        this.fillHiddenFields(location);

        this.clearResults();
    }

    async fetchPostcodeFromCoordinates(longitude, latitude) {
        const params = new URLSearchParams({
            access_token: this.tokenValue,
            language: this.languageValue || 'fr',
            limit: '1',
            types: 'postcode',
        });

        const url = `https://api.mapbox.com/geocoding/v5/mapbox.places/${longitude},${latitude}.json?${params.toString()}`;

        try {
            const response = await fetch(url);

            if (!response.ok) {
                return '';
            }

            const data = await response.json();
            const feature = data.features?.[0];

            if (!feature) {
                return '';
            }

            return feature.text || '';
        } catch (error) {
            console.error(error);
            return '';
        }
    }

    fillHiddenFields(location) {
        this.setTargetValue('selectedValue', location.value);
        this.setTargetValue('selectedMapboxId', location.mapboxId);
        this.setTargetValue('selectedFeatureType', location.featureType);

        this.setTargetValue('selectedCountryName', location.countryName);
        this.setTargetValue('selectedCountryCode', location.countryCode);
        this.setTargetValue('selectedRegionName', location.regionName);
        this.setTargetValue('selectedCityName', location.cityName);
        this.setTargetValue('selectedPostalCode', location.postalCode);

        this.setTargetValue('selectedLatitude', location.latitude);
        this.setTargetValue('selectedLongitude', location.longitude);
        this.setTargetValue('selectedFullAddress', location.fullAddress);
        this.setTargetValue('selectedLocale', this.languageValue || 'fr');

        this.setTargetValue('selectedLocationJson', JSON.stringify(location));
    }

    clearHiddenFields() {
        this.setTargetValue('selectedValue', '');
        this.setTargetValue('selectedMapboxId', '');
        this.setTargetValue('selectedFeatureType', '');

        this.setTargetValue('selectedCountryName', '');
        this.setTargetValue('selectedCountryCode', '');
        this.setTargetValue('selectedRegionName', '');
        this.setTargetValue('selectedCityName', '');
        this.setTargetValue('selectedPostalCode', '');

        this.setTargetValue('selectedLatitude', '');
        this.setTargetValue('selectedLongitude', '');
        this.setTargetValue('selectedFullAddress', '');
        this.setTargetValue('selectedLocale', this.languageValue || 'fr');
        this.setTargetValue('selectedLocationJson', '');
    }

    setTargetValue(targetName, value) {
        const methodName = `has${this.upperFirst(targetName)}Target`;
        const propertyName = `${targetName}Target`;

        if (!this[methodName]) {
            return;
        }

        this[propertyName].value = value ?? '';
    }

    buildLocation(feature) {
        const context = feature.context || [];
        const featureType = feature.place_type?.[0] || '';
        const coordinates = feature.center || [];
        const featureText = feature.text || '';
        const fullAddress = feature.place_name || featureText;

        const country = this.findContext(context, 'country');
        const region = this.findContext(context, 'region');
        const place = this.findContext(context, 'place');
        const locality = this.findContext(context, 'locality');
        const postcode = this.findContext(context, 'postcode');

        let value = featureText;

        let countryName = country?.text || '';
        let countryCode = country?.short_code || '';
        let regionName = region?.text || '';
        let cityName = '';
        let postalCode = '';

        /*
         * Cas 1 : l'utilisateur sélectionne un pays.
         */
        if (featureType === 'country') {
            value = featureText;
            countryName = featureText;
            countryCode = feature.properties?.short_code || countryCode;
        }

        /*
         * Cas 2 : l'utilisateur sélectionne une ville.
         */
        if (featureType === 'place' || featureType === 'locality') {
            value = featureText;
            cityName = featureText;
            postalCode = postcode?.text || '';
        }

        /*
         * Cas 3 : l'utilisateur sélectionne un code postal.
         */
        if (featureType === 'postcode') {
            value = featureText;
            postalCode = featureText;
            cityName = place?.text || locality?.text || '';
        }

        /*
         * Sécurité :
         * si la ville existe dans le contexte mais pas dans le type principal.
         */
        if (!cityName) {
            cityName = place?.text || locality?.text || '';
        }

        /*
         * Sécurité :
         * si le pays est directement le résultat sélectionné.
         */
        if (!countryName && featureType === 'country') {
            countryName = featureText;
        }

        return {
            value: value,
            mapboxId: feature.id || '',
            featureType: featureType,

            countryName: countryName,
            countryCode: countryCode ? countryCode.toUpperCase() : '',
            regionName: regionName,
            cityName: cityName,
            postalCode: postalCode,

            latitude: coordinates[1] || '',
            longitude: coordinates[0] || '',
            fullAddress: fullAddress,
        };
    }

    findContext(context, type) {
        return context.find((item) => {
            return item.id && item.id.startsWith(`${type}.`);
        });
    }

    clearResults() {
        this.resultsTarget.innerHTML = '';
        this.resultsTarget.classList.remove('is-active');
    }

    handleClickOutside(event) {
        if (!this.element.contains(event.target)) {
            this.clearResults();
        }
    }

    escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value || '';

        return div.innerHTML;
    }

    upperFirst(value) {
        return value.charAt(0).toUpperCase() + value.slice(1);
    }
}