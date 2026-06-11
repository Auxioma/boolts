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

    static values = {
        debug: {
            type: Boolean,
            default: true,
        },
        limit: {
            type: Number,
            default: 10,
        },
        lang: {
            type: String,
            default: 'fr',
        },
        osmTag: {
            type: String,
            default: '',
        },
    };

    connect() {
        this.timeout = null;
        this.abortController = null;

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
        if (!this.hasAdresseTarget || event.currentTarget !== this.adresseTarget) {
            this.hideResults();
            return;
        }

        clearTimeout(this.timeout);

        const query = this.adresseTarget.value.trim();

        if (query.length < 3) {
            this.hideResults();
            return;
        }

        this.timeout = setTimeout(() => {
            this.fetchAddresses(query);
        }, 300);
    }

    async fetchAddresses(query) {
        try {
            if (this.abortController) {
                this.abortController.abort();
            }

            this.abortController = new AbortController();

            const url = this.buildPhotonUrl(query);

            const response = await fetch(url, {
                signal: this.abortController.signal,
            });

            if (!response.ok) {
                this.hideResults();
                return;
            }

            const data = await response.json();

            this.debugApiResponse(query, url, data);

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

    buildPhotonUrl(query) {
        const params = new URLSearchParams();

        params.set('q', query);
        params.set('limit', String(this.limitValue));
        params.set('lang', this.langValue);

        /*
         * Exemple :
         * data-mes-biens--adresse-autocomplete-osm-tag-value="amenity:school"
         */
        if (this.osmTagValue) {
            params.set('osm_tag', this.osmTagValue);
        }

        return `https://photon.komoot.io/api/?${params.toString()}`;
    }

    renderResults(features) {
        this.resultsElement.innerHTML = '';

        const parent = this.adresseTarget.closest('.js-address-autocomplete-field');

        if (!parent) {
            return;
        }

        parent.classList.add('position-relative');
        parent.appendChild(this.resultsElement);

        features.forEach((feature, index) => {
            const props = feature.properties || {};

            this.debugRenderedOption(feature, index);

            const label = this.buildResultLabel(feature);

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

        this.debugSelectedOption(feature);

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

    buildResultLabel(feature) {
        const props = feature.properties || {};

        const rue = this.buildStreet(props);
        const ville = this.getCity(props);
        const codePostal = props.postcode || '';
        const pays = props.country || '';

        const poiType = this.getPoiLabel(props);

        return [
            rue || props.name,
            poiType,
            codePostal,
            ville,
            pays,
        ]
            .filter(Boolean)
            .join(', ');
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

    getPoiLabel(props) {
        if (!props.osm_key || !props.osm_value) {
            return '';
        }

        const key = props.osm_key;
        const value = props.osm_value;

        const labels = {
            'amenity:school': 'École',
            'amenity:kindergarten': 'Maternelle / crèche',
            'amenity:college': 'Collège / établissement',
            'amenity:university': 'Université',
            'amenity:hospital': 'Hôpital',
            'amenity:pharmacy': 'Pharmacie',
            'amenity:restaurant': 'Restaurant',
            'amenity:cafe': 'Café',
            'tourism:museum': 'Musée',
            'tourism:hotel': 'Hôtel',
            'shop:supermarket': 'Supermarché',
        };

        return labels[`${key}:${value}`] || `${key}:${value}`;
    }

    isPoi(props) {
        if (!props.osm_key || !props.osm_value) {
            return false;
        }

        const addressKeys = [
            'place',
            'highway',
            'boundary',
        ];

        return !addressKeys.includes(props.osm_key);
    }

    isSchool(props) {
        return props.osm_key === 'amenity'
            && props.osm_value === 'school';
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
        const isInsideController = this.element.contains(event.target);

        if (isInsideResults || isInsideController) {
            return;
        }

        this.hideResults();
    }

    debugApiResponse(query, url, data) {
        if (!this.debugValue) {
            return;
        }

        console.group('🌍 API Photon - Réponse complète');

        console.log('Recherche :', query);
        console.log('URL appelée :', url);
        console.log('Réponse brute complète :', data);
        console.log('Type GeoJSON :', data.type || null);
        console.log('Nombre de résultats :', data.features ? data.features.length : 0);

        if (!data.features || data.features.length === 0) {
            console.log('Aucun résultat.');
            console.groupEnd();
            return;
        }

        const resume = data.features.map((feature, index) => {
            const props = feature.properties || {};
            const geometry = feature.geometry || {};
            const coordinates = geometry.coordinates || [];

            return {
                index: index + 1,

                name: props.name || '',
                label: props.label || '',

                rue: this.buildStreet(props),
                postcode: props.postcode || '',
                ville: this.getCity(props),
                country: props.country || '',
                countrycode: props.countrycode || '',

                longitude: coordinates[0] ?? '',
                latitude: coordinates[1] ?? '',

                geometry_type: geometry.type || '',
                feature_type: feature.type || '',

                osm_key: props.osm_key || '',
                osm_value: props.osm_value || '',
                osm_type: props.osm_type || '',
                osm_id: props.osm_id || '',

                poi: this.isPoi(props) ? 'oui' : 'non',
                school: this.isSchool(props) ? 'oui' : 'non',
                poi_label: this.getPoiLabel(props),

                extent: props.extent ? JSON.stringify(props.extent) : '',
                properties_keys: Object.keys(props).join(', '),
            };
        });

        console.group('📋 Résumé des options');
        console.table(resume);
        console.groupEnd();

        data.features.forEach((feature, index) => {
            this.debugFeature(feature, index);
        });

        console.groupEnd();
    }

    debugRenderedOption(feature, index) {
        if (!this.debugValue) {
            return;
        }

        const props = feature.properties || {};

        console.group(`👁️ Option affichée ${index + 1}`);
        console.log('Label affiché :', this.buildResultLabel(feature));
        console.log('Feature complète :', feature);
        console.log('Properties :', props);
        console.table(this.prepareObjectForTable(props));
        console.groupEnd();
    }

    debugSelectedOption(feature) {
        if (!this.debugValue) {
            return;
        }

        console.group('✅ Option sélectionnée');
        this.debugFeature(feature, 0);
        console.groupEnd();
    }

    debugFeature(feature, index) {
        const props = feature.properties || {};
        const geometry = feature.geometry || {};
        const coordinates = geometry.coordinates || [];
        const flatFeature = this.flattenObject(feature);

        console.group(`📍 Détail option API ${index + 1}`);

        console.log('Feature complète :', feature);
        console.log('Properties complètes :', props);
        console.log('Geometry complète :', geometry);
        console.log('Coordonnées :', coordinates);

        console.group('🧭 Géolocalisation');
        console.log({
            longitude: coordinates[0] ?? null,
            latitude: coordinates[1] ?? null,
            geometry_type: geometry.type || null,
            extent: props.extent || null,
        });
        console.groupEnd();

        console.group('🏢 POI / École / OSM');
        console.log({
            is_poi: this.isPoi(props),
            is_school: this.isSchool(props),
            poi_label: this.getPoiLabel(props),
            osm_key: props.osm_key || null,
            osm_value: props.osm_value || null,
            osm_type: props.osm_type || null,
            osm_id: props.osm_id || null,
        });
        console.groupEnd();

        console.group('🏠 Adresse détaillée');
        console.log({
            name: props.name || null,
            label: props.label || null,
            housenumber: props.housenumber || null,
            street: props.street || null,
            postcode: props.postcode || null,
            city: props.city || null,
            town: props.town || null,
            village: props.village || null,
            municipality: props.municipality || null,
            district: props.district || null,
            county: props.county || null,
            state: props.state || null,
            country: props.country || null,
            countrycode: props.countrycode || null,
        });
        console.groupEnd();

        console.group('📦 Toutes les properties');
        console.table(this.prepareObjectForTable(props));
        console.groupEnd();

        console.group('🧩 Feature complète aplatie');
        console.table(this.prepareObjectForTable(flatFeature));
        console.groupEnd();

        console.groupEnd();
    }

    flattenObject(object, prefix = '', result = {}) {
        Object.entries(object || {}).forEach(([key, value]) => {
            const path = prefix ? `${prefix}.${key}` : key;

            if (
                value !== null
                && typeof value === 'object'
                && !Array.isArray(value)
            ) {
                this.flattenObject(value, path, result);
                return;
            }

            result[path] = Array.isArray(value)
                ? JSON.stringify(value)
                : value;
        });

        return result;
    }

    prepareObjectForTable(object) {
        return Object.entries(object || {}).map(([key, value]) => {
            return {
                champ: key,
                valeur: this.formatConsoleValue(value),
                type: Array.isArray(value) ? 'array' : typeof value,
            };
        });
    }

    formatConsoleValue(value) {
        if (value === null) {
            return null;
        }

        if (typeof value === 'undefined') {
            return undefined;
        }

        if (Array.isArray(value) || typeof value === 'object') {
            return JSON.stringify(value);
        }

        return value;
    }
}
