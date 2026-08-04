import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['menu', 'button'];

    connect() {
        this.closeOnOutsideClick = this.closeOnOutsideClick.bind(this);
    }

    toggle(event) {
        event.preventDefault();
        event.stopPropagation();

        if (this.menuTarget.classList.contains('d-none')) {
            this.open();
            return;
        }

        this.close();
    }

    open() {
        this.menuTarget.classList.remove('d-none');
        this.buttonTarget.setAttribute('aria-expanded', 'true');

        document.addEventListener('click', this.closeOnOutsideClick);
    }

    close() {
        this.menuTarget.classList.add('d-none');
        this.buttonTarget.setAttribute('aria-expanded', 'false');

        document.removeEventListener('click', this.closeOnOutsideClick);
    }

    selectOption(event) {
        const selectedOption = event.currentTarget;

        this.menuTarget.querySelectorAll('[role="option"]').forEach((option) => {
            option.classList.remove('sort-menu__item--active');
            option.setAttribute('aria-selected', 'false');
        });

        selectedOption.classList.add('sort-menu__item--active');
        selectedOption.setAttribute('aria-selected', 'true');
        this.close();
    }

    closeOnOutsideClick(event) {
        if (!this.element.contains(event.target)) {
            this.close();
        }
    }

    disconnect() {
        document.removeEventListener('click', this.closeOnOutsideClick);
    }
}
