import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['select', 'search', 'results', 'tags'];

    connect() {
        this.renderTags();
    }

    search() {
        const term = this.searchTarget.value.trim().toLowerCase();

        this.resultsTarget.innerHTML = '';

        if (term.length < 1) {
            return;
        }

        const options = Array.from(this.selectTarget.options);

        const results = options.filter(option => {
            return option.text.toLowerCase().includes(term) && !option.selected;
        });

        this.resultsTarget.innerHTML = results.map(option => `
            <button
                type="button"
                class="agency-languages-result"
                data-value="${option.value}"
            >
                ${option.text}
            </button>
        `).join('');

        this.resultsTarget.querySelectorAll('button').forEach(button => {
            button.addEventListener('click', () => {
                this.selectLanguage(button.dataset.value);
            });
        });
    }

    addFirstResult() {
        const firstResult = this.resultsTarget.querySelector('button');

        if (!firstResult) {
            return;
        }

        this.selectLanguage(firstResult.dataset.value);
    }

    selectLanguage(value) {
        const option = this.selectTarget.querySelector(`option[value="${value}"]`);

        if (!option) {
            return;
        }

        option.selected = true;

        this.searchTarget.value = '';
        this.resultsTarget.innerHTML = '';

        this.selectTarget.dispatchEvent(new Event('change', { bubbles: true }));

        this.renderTags();
    }

    remove(event) {
        const value = event.currentTarget.dataset.value;
        const option = this.selectTarget.querySelector(`option[value="${value}"]`);

        if (!option) {
            return;
        }

        option.selected = false;

        this.selectTarget.dispatchEvent(new Event('change', { bubbles: true }));

        this.renderTags();
    }

    renderTags() {
        this.tagsTarget.innerHTML = '';

        Array.from(this.selectTarget.selectedOptions).forEach(option => {
            const tag = document.createElement('button');

            tag.type = 'button';
            tag.className = 'agency-language-tag';
            tag.dataset.action = 'click->languages#remove';
            tag.dataset.value = option.value;
            tag.innerHTML = `${option.text} <span>×</span>`;

            this.tagsTarget.appendChild(tag);
        });
    }
}