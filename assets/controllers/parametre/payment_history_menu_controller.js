import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.clickListener = (event) => {
            this.handleClick(event);
        };

        document.addEventListener('click', this.clickListener);
    }

    disconnect() {
        if (this.clickListener) {
            document.removeEventListener('click', this.clickListener);
        }
    }

    handleClick(event) {
        const toggleButton = event.target.closest('.js-payment-menu-toggle');

        this.element.querySelectorAll('.js-payment-line').forEach((line) => {
            const menu = line.querySelector('.js-payment-menu');
            const button = line.querySelector('.js-payment-menu-toggle');

            if (!menu || !button) {
                return;
            }

            if (!toggleButton || !line.contains(toggleButton)) {
                menu.classList.add('d-none');
                button.setAttribute('aria-expanded', 'false');
            }
        });

        if (!toggleButton || !this.element.contains(toggleButton)) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const line = toggleButton.closest('.js-payment-line');
        const menu = line.querySelector('.js-payment-menu');

        if (!menu) {
            return;
        }

        const isOpen = !menu.classList.contains('d-none');

        menu.classList.toggle('d-none', isOpen);
        toggleButton.setAttribute('aria-expanded', isOpen ? 'false' : 'true');
    }
}
