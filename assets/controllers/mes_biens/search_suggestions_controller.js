import { Controller } from '@hotwired/stimulus';

/**
 * Auto-suggestion de la barre de recherche libre de la liste « Mes biens ».
 *
 * Les propositions proviennent de l'endpoint
 * agence_immobiliere_mes_biens_search_suggestions, qui puise dans les mêmes
 * colonnes que le LIKE SQL de la recherche (référence, titre, ville, pays,
 * adresse). Choisir une proposition remplit le champ et soumet le formulaire.
 */
export default class extends Controller {
    static targets = ['input', 'results'];

    static values = {
        url: String,
        minLength: {
            type: Number,
            default: 2,
        },
        debounce: {
            type: Number,
            default: 200,
        },
    };

    connect() {
        this.timeout = null;
        this.abortController = null;
        this.activeIndex = -1;
        this.suggestions = [];

        this.onOutsideClick = this.onOutsideClick.bind(this);
        document.addEventListener('click', this.onOutsideClick);

        this.hideResults();
    }

    disconnect() {
        document.removeEventListener('click', this.onOutsideClick);
        this.abortCurrentRequest();

        if (this.timeout) {
            clearTimeout(this.timeout);
        }
    }

    onInput() {
        const query = this.inputTarget.value.trim();

        if (this.timeout) {
            clearTimeout(this.timeout);
        }

        if (query.length < this.minLengthValue) {
            this.abortCurrentRequest();
            this.clearResults();
            return;
        }

        this.timeout = setTimeout(() => {
            this.fetchSuggestions(query);
        }, this.debounceValue);
    }

    async fetchSuggestions(query) {
        if (!this.hasUrlValue) {
            return;
        }

        this.abortCurrentRequest();
        this.abortController = new AbortController();

        const url = new URL(this.urlValue, window.location.origin);
        url.searchParams.set('q', query);

        try {
            const response = await fetch(url.toString(), {
                method: 'GET',
                signal: this.abortController.signal,
                headers: {
                    Accept: 'application/json',
                },
            });

            if (!response.ok) {
                this.clearResults();
                return;
            }

            const data = await response.json();

            if (this.inputTarget.value.trim() !== query) {
                return;
            }

            this.suggestions = Array.isArray(data.results) ? data.results : [];
            this.activeIndex = -1;
            this.renderResults();
        } catch (error) {
            if (error.name !== 'AbortError') {
                this.clearResults();
            }
        }
    }

    renderResults() {
        if (!this.hasResultsTarget) {
            return;
        }

        this.resultsTarget.innerHTML = '';

        if (this.suggestions.length === 0) {
            this.hideResults();
            return;
        }

        this.suggestions.forEach((suggestion, index) => {
            const value = this.suggestionValue(suggestion);

            if ('' === value) {
                return;
            }

            const type = typeof suggestion === 'object' && suggestion
                ? suggestion.type
                : '';

            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'property-search-suggestion';
            button.dataset.index = String(index);
            button.innerHTML = `
                <span class="property-search-suggestion__value">${this.escapeHtml(value)}</span>
                ${type ? `<span class="property-search-suggestion__type">${this.escapeHtml(this.typeLabel(type))}</span>` : ''}
            `;

            button.addEventListener('mousedown', (event) => {
                event.preventDefault();
            });

            button.addEventListener('click', (event) => {
                event.preventDefault();
                this.choose(index);
            });

            this.resultsTarget.appendChild(button);
        });

        this.showResults();
    }

    onKeydown(event) {
        if (!this.hasResultsTarget || this.resultsTarget.hidden) {
            return;
        }

        const items = Array.from(
            this.resultsTarget.querySelectorAll('.property-search-suggestion')
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
                this.choose(this.activeIndex);
            }

            return;
        }

        if (event.key === 'Escape') {
            this.hideResults();
        }
    }

    updateActiveItem(items) {
        items.forEach((item, index) => {
            if (index === this.activeIndex) {
                item.classList.add('is-active');
                item.scrollIntoView({
                    block: 'nearest',
                });
            } else {
                item.classList.remove('is-active');
            }
        });
    }

    choose(index) {
        const value = this.suggestionValue(this.suggestions[index]);

        if ('' === value) {
            return;
        }

        this.inputTarget.value = value;
        this.clearResults();

        if (!(this.element instanceof HTMLFormElement)) {
            return;
        }

        if (typeof this.element.requestSubmit === 'function') {
            this.element.requestSubmit();
        } else {
            this.element.submit();
        }
    }

    onOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.hideResults();
        }
    }

    showResults() {
        if (this.hasResultsTarget) {
            this.resultsTarget.hidden = false;
        }
    }

    hideResults() {
        if (this.hasResultsTarget) {
            this.resultsTarget.hidden = true;
        }

        this.activeIndex = -1;
    }

    clearResults() {
        this.suggestions = [];

        if (this.hasResultsTarget) {
            this.resultsTarget.innerHTML = '';
        }

        this.hideResults();
    }

    abortCurrentRequest() {
        if (this.abortController) {
            this.abortController.abort();
            this.abortController = null;
        }
    }

    suggestionValue(suggestion) {
        if (typeof suggestion === 'string') {
            return suggestion.trim();
        }

        if (suggestion && typeof suggestion.value === 'string') {
            return suggestion.value.trim();
        }

        return '';
    }

    typeLabel(type) {
        const labels = {
            reference: 'Référence',
            titre: 'Titre',
            ville: 'Ville',
            pays: 'Pays',
            adresse: 'Adresse',
        };

        return labels[type] || '';
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
