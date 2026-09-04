import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['select', 'search', 'results'];

    static values = {
        noResultsText: String
    };

    connect() {
        this.syncSearchWithSelectedOption();
    }

    search() {
        const term = this.searchTarget.value.trim().toLowerCase();

        if (term.length === 0) {
            this.clearSelectedOption();
            this.renderResults(this.availableOptions().slice(0, 8));

            return;
        }

        const results = this.availableOptions().filter((option) => {
            return option.text.toLowerCase().includes(term);
        });

        this.renderResults(results.slice(0, 8));
    }

    show() {
        const term = this.searchTarget.value.trim().toLowerCase();
        const results = term.length === 0
            ? this.availableOptions().slice(0, 8)
            : this.availableOptions()
                .filter((option) => option.text.toLowerCase().includes(term))
                .slice(0, 8);

        this.renderResults(results);
    }

    handleKeydown(event) {
        if (event.key !== 'Enter') {
            return;
        }

        const firstResult = this.resultsTarget.querySelector('.agency-autocomplete-result');

        if (!firstResult) {
            return;
        }

        event.preventDefault();
        this.selectOption(firstResult.dataset.value);
    }

    choose(event) {
        event.preventDefault();
        this.selectOption(event.currentTarget.dataset.value);
    }

    hideLater() {
        window.setTimeout(() => {
            this.clearResults();
            this.syncSearchWithSelectedOption();
        }, 150);
    }

    focusSearch(event) {
        window.setTimeout(() => {
            if (event.currentTarget.dataset.fieldSaveState !== 'editing') {
                return;
            }

            if (this.searchTarget.closest('.d-none')) {
                return;
            }

            this.searchTarget.focus();
            this.searchTarget.select();
        }, 0);
    }

    selectOption(value) {
        const option = this.availableOptions().find((item) => item.value === value);

        if (!option) {
            return;
        }

        this.selectTarget.value = option.value;
        this.searchTarget.value = option.text.trim();

        this.clearResults();
        this.dispatchSelectChange();
    }

    clearSelectedOption() {
        if (this.selectTarget.value === '') {
            return;
        }

        this.selectTarget.value = '';
        this.dispatchSelectChange();
    }

    dispatchSelectChange() {
        this.selectTarget.dispatchEvent(new Event('input', { bubbles: true }));
        this.selectTarget.dispatchEvent(new Event('change', { bubbles: true }));
    }

    syncSearchWithSelectedOption() {
        const selectedOption = this.selectTarget.selectedOptions[0];

        this.searchTarget.value = selectedOption && selectedOption.value !== ''
            ? selectedOption.text.trim()
            : '';
    }

    availableOptions() {
        return Array.from(this.selectTarget.options).filter((option) => {
            return option.value !== '';
        });
    }

    renderResults(options) {
        this.resultsTarget.innerHTML = '';

        if (options.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'agency-autocomplete-empty';
            empty.textContent = this.hasNoResultsTextValue
                ? this.noResultsTextValue
                : 'Aucun résultat trouvé';

            this.resultsTarget.appendChild(empty);
            this.resultsTarget.classList.remove('d-none');

            return;
        }

        options.forEach((option) => {
            const button = document.createElement('button');

            button.type = 'button';
            button.className = 'agency-autocomplete-result';
            button.dataset.value = option.value;
            button.dataset.action = `mousedown->${this.identifier}#choose`;
            button.textContent = option.text.trim();

            this.resultsTarget.appendChild(button);
        });

        this.resultsTarget.classList.remove('d-none');
    }

    clearResults() {
        this.resultsTarget.innerHTML = '';
        this.resultsTarget.classList.add('d-none');
    }
}
