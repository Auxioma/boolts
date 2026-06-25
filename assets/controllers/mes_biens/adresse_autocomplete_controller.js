import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'adresse',
        'codePostal',
        'ville',
        'pays',
        'latitude',
        'longitude',
        'results',

        'sessionIdMapbox',

        'mapboxId',
        'fullAddress',
        'featureType',
        'region',
        'district',
        'locality',
        'neighborhood',
        'poi',
    ];

    static values = {
        token: String,
        language: {
            type: String,
            default: 'fr',
        },
        limit: {
            type: Number,
            default: 10,
        },
        types: {
            type: String,
            default: 'address,poi,street,place,locality,neighborhood,postcode,region,country',
        },
    };

    connect() {
        this.timeout = null;
        this.abortController = null;
        this.activeIndex = -1;
        this.suggestions = [];
        this.sessionToken = this.createSessionToken();
        this.isSelecting = false;

        this.setTargetValue('sessionIdMapbox', this.sessionToken);

        this.hideResults();
    }

    disconnect() {
        this.abortCurrentRequest();

        if (this.timeout) {
            clearTimeout(this.timeout);
        }
    }

    search() {
        if (this.isSelecting) {
            return;
        }

        const query = this.adresseTarget.value.trim();

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        if (query.length < 2) {
            this.clearSuggestions();
            this.clearHiddenFields();
            return;
        }

        this.timeout = setTimeout(() => {
            if (!this.isSelecting) {
                this.fetchSuggestions(query);
            }
        }, 300);
    }

    async fetchSuggestions(query) {
        if (this.isSelecting) {
            return;
        }

        this.abortCurrentRequest();
        this.abortController = new AbortController();

        const url = new URL('https://api.mapbox.com/search/searchbox/v1/suggest');

        url.searchParams.set('q', query);
        url.searchParams.set('access_token', this.tokenValue);
        url.searchParams.set('session_token', this.sessionToken);
        url.searchParams.set('language', this.languageValue);
        url.searchParams.set('limit', String(this.limitValue));

        if (this.typesValue) {
            url.searchParams.set('types', this.typesValue);
        }

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                signal: this.abortController.signal,
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                console.error('Erreur Mapbox suggest :', response.status, response.statusText);
                this.clearSuggestions();
                return;
            }

            const data = await response.json();

            this.suggestions = Array.isArray(data.suggestions)
                ? data.suggestions
                : [];

            this.activeIndex = -1;
            this.renderSuggestions();
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('Erreur requête Mapbox suggest :', error);
            this.clearSuggestions();
        }
    }

    renderSuggestions() {
        if (!this.hasResultsTarget) {
            return;
        }

        this.resultsTarget.innerHTML = '';

        if (this.suggestions.length === 0) {
            this.hideResults();
            return;
        }

        this.suggestions.forEach((suggestion, index) => {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'list-group-item list-group-item-action mapbox-autocomplete-result';
            button.dataset.index = String(index);
            button.innerHTML = this.renderSuggestionHtml(suggestion);

            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            button.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                this.selectSuggestion(index);
            });

            this.resultsTarget.appendChild(button);
        });

        this.showResults();
    }

    renderSuggestionHtml(suggestion) {
        const name = this.escapeHtml(
            suggestion.name ||
            suggestion.name_preferred ||
            suggestion.text ||
            ''
        );

        const fullAddress = this.escapeHtml(
            suggestion.full_address ||
            suggestion.place_formatted ||
            suggestion.address ||
            ''
        );

        const featureType = this.escapeHtml(
            suggestion.feature_type ||
            suggestion.type ||
            ''
        );

        return `
            <div class="d-flex flex-column">
                <strong>${name}</strong>
                ${fullAddress ? `<small>${fullAddress}</small>` : ''}
                ${featureType ? `<small class="text-muted">${featureType}</small>` : ''}
            </div>
        `;
    }

    async selectSuggestion(index) {
        const suggestion = this.suggestions[index];

        if (!suggestion) {
            return;
        }

        this.isSelecting = true;
        this.clearPendingSearch();
        this.hideResults();

        const mapboxId = suggestion.mapbox_id || suggestion.id || '';

        if (!mapboxId) {
            console.error('Aucun mapbox_id trouvé dans la suggestion :', suggestion);
            this.isSelecting = false;
            return;
        }

        await this.retrieveFeature(mapboxId, suggestion);
    }

    async retrieveFeature(mapboxId, suggestion = null) {
        this.abortCurrentRequest();
        this.abortController = new AbortController();

        const url = new URL(
            `https://api.mapbox.com/search/searchbox/v1/retrieve/${encodeURIComponent(mapboxId)}`
        );

        url.searchParams.set('access_token', this.tokenValue);
        url.searchParams.set('session_token', this.sessionToken);
        url.searchParams.set('language', this.languageValue);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                signal: this.abortController.signal,
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                console.error('Erreur Mapbox retrieve :', response.status, response.statusText);
                this.isSelecting = false;
                return;
            }

            const data = await response.json();
            const feature = this.getFirstFeature(data);

            if (!feature) {
                console.error('Aucune feature Mapbox récupérée :', data);
                this.isSelecting = false;
                return;
            }

            this.fillFieldsFromFeature(feature, suggestion);

            this.clearSuggestions();
            this.hideResults();

            this.resetSessionToken();

            setTimeout(() => {
                this.isSelecting = false;
            }, 300);
        } catch (error) {
            if (error.name === 'AbortError') {
                return;
            }

            console.error('Erreur requête Mapbox retrieve :', error);
            this.isSelecting = false;
        }
    }

    fillFieldsFromFeature(feature, suggestion = null) {
        const properties = feature.properties || {};
        const context = properties.context || {};
        const coordinates = feature.geometry?.coordinates || [];

        const longitude = coordinates[0] ?? '';
        const latitude = coordinates[1] ?? '';

        const mapboxId =
            properties.mapbox_id ||
            suggestion?.mapbox_id ||
            feature.id ||
            '';

        const featureType =
            properties.feature_type ||
            suggestion?.feature_type ||
            '';

        const name =
            properties.name ||
            properties.name_preferred ||
            suggestion?.name ||
            suggestion?.name_preferred ||
            '';

        const fullAddress =
            properties.full_address ||
            properties.place_formatted ||
            suggestion?.full_address ||
            suggestion?.place_formatted ||
            name ||
            '';

        const country = this.getContextName(context, 'country');
        const countryCode = this.getContextCode(context, 'country');

        const postcode = this.getContextName(context, 'postcode');
        const region = this.getContextName(context, 'region');
        const district = this.getContextName(context, 'district');
        const place = this.getContextName(context, 'place');
        const locality = this.getContextName(context, 'locality');
        const neighborhood = this.getContextName(context, 'neighborhood');

        const poi = featureType === 'poi' ? name : '';

        this.setTargetValue('adresse', fullAddress || name);
        this.setTargetValue('codePostal', postcode);
        this.setTargetValue('ville', place || locality || district || neighborhood || name);

        this.setSelectValueByTextOrValue('pays', country, countryCode);

        this.setTargetValue('latitude', latitude);
        this.setTargetValue('longitude', longitude);

        this.setTargetValue('mapboxId', mapboxId);
        this.setTargetValue('fullAddress', fullAddress);
        this.setTargetValue('featureType', featureType);
        this.setTargetValue('region', region);
        this.setTargetValue('district', district);
        this.setTargetValue('locality', locality);
        this.setTargetValue('neighborhood', neighborhood);
        this.setTargetValue('poi', poi);
    }

    getFirstFeature(data) {
        if (Array.isArray(data.features) && data.features.length > 0) {
            return data.features[0];
        }

        if (data.feature) {
            return data.feature;
        }

        if (data.type === 'Feature') {
            return data;
        }

        return null;
    }

    getContextName(context, key) {
        if (!context || !key) {
            return '';
        }

        if (context[key]?.name) {
            return context[key].name;
        }

        if (context[key]?.text) {
            return context[key].text;
        }

        if (Array.isArray(context)) {
            const item = context.find((element) => {
                return element.id?.startsWith(`${key}.`) || element.type === key;
            });

            return item?.name || item?.text || '';
        }

        return '';
    }

    getContextCode(context, key) {
        if (!context || !key) {
            return '';
        }

        if (context[key]?.country_code) {
            return context[key].country_code;
        }

        if (context[key]?.short_code) {
            return context[key].short_code;
        }

        if (context[key]?.code) {
            return context[key].code;
        }

        if (Array.isArray(context)) {
            const item = context.find((element) => {
                return element.id?.startsWith(`${key}.`) || element.type === key;
            });

            return item?.country_code || item?.short_code || item?.code || '';
        }

        return '';
    }

    setSelectValueByTextOrValue(targetName, searchedText, searchedCode = '') {
        const hasTargetName = `has${this.capitalize(targetName)}Target`;
        const targetElementName = `${targetName}Target`;

        if (!this[hasTargetName] || !this[targetElementName]) {
            return;
        }

        const element = this[targetElementName];

        if (!(element instanceof HTMLSelectElement)) {
            this.setTargetValue(targetName, searchedText);
            return;
        }

        const normalizedText = this.normalizeText(searchedText);
        const normalizedCode = this.normalizeText(searchedCode);

        if (!normalizedText && !normalizedCode) {
            return;
        }

        const options = Array.from(element.options);

        const matchingOption = options.find((option) => {
            const optionValue = this.normalizeText(option.value);
            const optionText = this.normalizeText(option.textContent);

            return optionValue === normalizedText ||
                optionText === normalizedText ||
                optionValue === normalizedCode ||
                optionText === normalizedCode;
        });

        if (!matchingOption) {
            console.warn('Aucune option trouvée dans le select pays pour :', {
                searchedText,
                searchedCode,
            });

            return;
        }

        element.value = matchingOption.value;
        this.dispatchChangeEvent(element);
    }

    onKeydown(event) {
        if (!this.hasResultsTarget || this.resultsTarget.hidden) {
            return;
        }

        const items = Array.from(
            this.resultsTarget.querySelectorAll('.mapbox-autocomplete-result')
        );

        if (items.length === 0) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            this.activeIndex = this.activeIndex + 1 >= items.length ? 0 : this.activeIndex + 1;
            this.updateActiveItem(items);
            return;
        }

        if (event.key === 'ArrowUp') {
            event.preventDefault();
            this.activeIndex = this.activeIndex - 1 < 0 ? items.length - 1 : this.activeIndex - 1;
            this.updateActiveItem(items);
            return;
        }

        if (event.key === 'Enter') {
            if (this.activeIndex >= 0 && this.activeIndex < this.suggestions.length) {
                event.preventDefault();
                this.selectSuggestion(this.activeIndex);
            }

            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            this.hideResults();
        }
    }

    updateActiveItem(items) {
        items.forEach((item, index) => {
            if (index === this.activeIndex) {
                item.classList.add('active');

                item.scrollIntoView({
                    block: 'nearest',
                });
            } else {
                item.classList.remove('active');
            }
        });
    }

    onBlur() {
        setTimeout(() => {
            if (!this.isSelecting) {
                this.hideResults();
            }
        }, 150);
    }

    showResults() {
        if (this.hasResultsTarget && !this.isSelecting) {
            this.resultsTarget.hidden = false;
        }
    }

    hideResults() {
        if (this.hasResultsTarget) {
            this.resultsTarget.hidden = true;
        }
    }

    clearSuggestions() {
        this.suggestions = [];
        this.activeIndex = -1;

        if (this.hasResultsTarget) {
            this.resultsTarget.innerHTML = '';
        }

        this.hideResults();
    }

    clearHiddenFields() {
        this.setTargetValue('mapboxId', '');
        this.setTargetValue('fullAddress', '');
        this.setTargetValue('featureType', '');
        this.setTargetValue('region', '');
        this.setTargetValue('district', '');
        this.setTargetValue('locality', '');
        this.setTargetValue('neighborhood', '');
        this.setTargetValue('poi', '');
        this.setTargetValue('latitude', '');
        this.setTargetValue('longitude', '');
    }

    clearPendingSearch() {
        if (this.timeout) {
            clearTimeout(this.timeout);
            this.timeout = null;
        }
    }

    setTargetValue(targetName, value) {
        const hasTargetName = `has${this.capitalize(targetName)}Target`;
        const targetElementName = `${targetName}Target`;

        if (this[hasTargetName] && this[targetElementName]) {
            this[targetElementName].value = value ?? '';
            this.dispatchChangeEvent(this[targetElementName]);
        }
    }

    getTargetValue(targetName) {
        const hasTargetName = `has${this.capitalize(targetName)}Target`;
        const targetElementName = `${targetName}Target`;

        if (this[hasTargetName] && this[targetElementName]) {
            return this[targetElementName].value;
        }

        return '';
    }

    dispatchChangeEvent(element) {
        element.dispatchEvent(new Event('change', {
            bubbles: true,
        }));
    }

    abortCurrentRequest() {
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }
    }

    resetSessionToken() {
        this.sessionToken = this.createSessionToken();
        this.setTargetValue('sessionIdMapbox', this.sessionToken);
    }

    createSessionToken() {
        if (window.crypto && typeof window.crypto.randomUUID === 'function') {
            return window.crypto.randomUUID();
        }

        return `session-${Date.now()}-${Math.random().toString(36).substring(2, 15)}`;
    }

    capitalize(value) {
        if (!value) {
            return '';
        }

        return value.charAt(0).toUpperCase() + value.slice(1);
    }

    normalizeText(value) {
        return String(value ?? '')
            .trim()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '');
    }

    escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }
}