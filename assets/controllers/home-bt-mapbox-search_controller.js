import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'input',
        'results',
        'pays',
        'ville',
        'cp',
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
    }

    search() {
        const query = this.inputTarget.value.trim();

        this.clearResults();

        if (query.length < 2) {
            this.clearInputs();
            return;
        }

        clearTimeout(this.timeout);

        this.timeout = setTimeout(() => {
            this.fetchMapboxResults(query);
        }, 350);
    }

    async fetchMapboxResults(query) {
        if (this.abortController) {
            this.abortController.abort();
        }

        this.abortController = new AbortController();

        const params = new URLSearchParams({
            q: query,
            access_token: this.tokenValue,
            autocomplete: 'true',
            language: this.languageValue,
            limit: '7',
            types: 'country,place,postcode',
        });

        try {
            const response = await fetch(
                `https://api.mapbox.com/search/geocode/v6/forward?${params.toString()}`,
                {
                    method: 'GET',
                    signal: this.abortController.signal,
                }
            );

            if (!response.ok) {
                this.clearResults();
                return;
            }

            const data = await response.json();

            this.renderResults(data.features || []);
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.clearResults();
            }
        }
    }

    renderResults(features) {
        this.clearResults();

        if (features.length === 0) {
            return;
        }

        features.forEach((feature) => {
            const values = this.getFeatureValues(feature);

            if (!values.label) {
                return;
            }

            const li = document.createElement('li');

            li.classList.add('home-bt-search-bar__result');
            li.textContent = values.label;

            li.addEventListener('click', () => {
                this.selectResult(feature);
            });

            this.resultsTarget.appendChild(li);
        });
    }

    selectResult(feature) {
        const values = this.getFeatureValues(feature);

        this.inputTarget.value = values.label;

        this.paysTarget.value = values.pays;
        this.villeTarget.value = values.ville;
        this.cpTarget.value = values.cp;

        this.clearResults();
    }

    getFeatureValues(feature) {
        const properties = feature.properties || {};
        const context = properties.context || {};
        const type = properties.feature_type;
        const name = properties.name || '';

        let pays = '';
        let ville = '';
        let cp = '';

        if (type === 'country') {
            pays = name;
        }

        if (type === 'place') {
            ville = name;
            pays = context.country?.name || '';
        }

        if (type === 'postcode') {
            cp = name;
            ville =
                context.place?.name ||
                context.locality?.name ||
                context.district?.name ||
                '';

            pays = context.country?.name || '';
        }

        const labelParts = [];

        if (cp) {
            labelParts.push(cp);
        }

        if (ville) {
            labelParts.push(ville);
        }

        if (pays) {
            labelParts.push(pays);
        }

        return {
            pays,
            ville,
            cp,
            label: labelParts.join(' — '),
        };
    }

    clearResults() {
        this.resultsTarget.innerHTML = '';
    }

    clearInputs() {
        this.paysTarget.value = '';
        this.villeTarget.value = '';
        this.cpTarget.value = '';
    }
}