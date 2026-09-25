import { Controller } from '@hotwired/stimulus';
import { Modal } from 'bootstrap';

export default class extends Controller {
    static targets = ['frame', 'modal', 'start', 'end', 'sortButton', 'sortMenu', 'filters', 'filterCount'];
    static values = { url: String };

    connect() {
        this.updateFilterCount(new URL(this.urlValue, window.location.origin).searchParams);
    }

    applyFilters(event) {
        event.preventDefault();
        const url = new URL(this.urlValue, window.location.origin);

        [...url.searchParams.keys()].forEach((key) => {
            if (key.startsWith('modal_filter[')) {
                url.searchParams.delete(key);
            }
        });
        new FormData(event.target).forEach((value, key) => {
            if (key.startsWith('modal_filter[')) {
                url.searchParams.append(key, value);
            }
        });

        this.urlValue = url.toString();
        this.load({ performance_page: 1 });
        this.updateFilterCount(url.searchParams);
        Modal.getOrCreateInstance(this.filtersTarget.querySelector('.modal')).hide();
    }

    updateFilterCount(parameters) {
        if (!this.hasFilterCountTarget) {
            return;
        }

        const groups = new Set();
        parameters.forEach((value, key) => {
            const match = key.match(/^modal_filter\[([^\]]+)\]/);
            if (!match || match[1].endsWith('Search') || !value || value === '[]') {
                return;
            }
            groups.add(match[1].replace(/^(min|max)/, ''));
        });
        this.filterCountTarget.textContent = groups.size;
        this.filterCountTarget.hidden = groups.size === 0;
    }

    syncFilterParameters() {
        if (!this.hasFiltersTarget) {
            return;
        }
        const url = new URL(this.urlValue, window.location.origin);
        this.filtersTarget.querySelectorAll('[data-performance-filter-parameter]').forEach((input) => {
            input.value = url.searchParams.get(input.name) || '';
            input.defaultValue = input.value;
        });
    }

    refreshFilterPreview() {
        this.syncFilterParameters();
        this.filtersTarget.querySelector('form').dispatchEvent(new Event('change', { bubbles: true }));
    }

    selectPeriod(event) {
        const period = event.currentTarget.dataset.period;

        if (period !== 'custom') {
            this.load({ period, performance_page: 1 });
        }
    }

    loadCustomPeriod() {
        if (!this.startTarget.value || !this.endTarget.value) {
            return;
        }

        this.load({
            period: 'custom',
            start: this.startTarget.value,
            end: this.endTarget.value,
            performance_page: 1,
        });
        Modal.getOrCreateInstance(this.modalTarget).hide();
    }

    toggleSortMenu(event) {
        event.preventDefault();

        if (this.sortMenuTarget.classList.contains('d-none')) {
            this.openSortMenu();
            return;
        }

        this.closeSortMenu();
    }

    selectSort(event) {
        const option = event.currentTarget;

        this.load({
            performance_sort: option.dataset.sort,
            performance_direction: option.dataset.direction,
            performance_page: 1,
        });
        this.setActiveSortOption(option.dataset.sort, option.dataset.direction);
        this.closeSortMenu();
    }

    load(parameters) {
        const url = new URL(this.urlValue, window.location.origin);

        Object.entries(parameters).forEach(([key, value]) => url.searchParams.set(key, value));

        if (parameters.period && parameters.period !== 'custom') {
            url.searchParams.delete('start');
            url.searchParams.delete('end');
        }

        url.searchParams.delete('sort');
        url.searchParams.delete('direction');

        this.urlValue = url.toString();
        this.syncFilterParameters();
        this.frameTarget.setAttribute('src', url.toString());
        const period = url.searchParams.get('period') || '30d';

        this.element.querySelectorAll('[data-period]').forEach((button) => {
            button.classList.toggle('active', button.dataset.period === period);
        });
    }

    syncPeriod(event) {
        if (event.detail.start && this.hasStartTarget) {
            this.startTarget.value = event.detail.start;
        }

        if (event.detail.end && this.hasEndTarget) {
            this.endTarget.value = event.detail.end;
        }

        this.load({ ...event.detail, performance_page: 1 });
    }

    openSortMenu() {
        this.sortMenuTarget.classList.remove('d-none');
        this.sortButtonTarget.setAttribute('aria-expanded', 'true');
    }

    closeSortMenu() {
        if (!this.hasSortMenuTarget) {
            return;
        }

        this.sortMenuTarget.classList.add('d-none');
        this.sortButtonTarget.setAttribute('aria-expanded', 'false');
    }

    closeSortMenuOnOutsideClick(event) {
        if (!this.hasSortMenuTarget || this.sortMenuTarget.classList.contains('d-none')) {
            return;
        }

        if (!this.element.contains(event.target)) {
            this.closeSortMenu();
        }
    }

    setActiveSortOption(sort, direction) {
        this.sortMenuTarget.querySelectorAll('[role="option"]').forEach((option) => {
            const isActive = option.dataset.sort === sort && option.dataset.direction === direction;

            option.classList.toggle('sort-menu__item--active', isActive);
            option.setAttribute('aria-selected', isActive ? 'true' : 'false');
        });
    }
}
