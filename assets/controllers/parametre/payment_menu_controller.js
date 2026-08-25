import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    connect() {
        this.menuButtons = [...this.element.querySelectorAll('[data-payment-menu-button]')];
        this.cleanups = [];

        this.menuButtons.forEach((button) => {
            const listener = (event) => {
                this.toggle(event, button);
            };

            button.addEventListener('click', listener);
            this.cleanups.push(() => button.removeEventListener('click', listener));
        });

        this.documentClickListener = (event) => {
            if (event.target.closest('.saved-payment-method__menu-wrapper')) {
                return;
            }

            this.closeAll();
        };

        this.keydownListener = (event) => {
            if (event.key === 'Escape') {
                this.closeAll();
            }
        };

        document.addEventListener('click', this.documentClickListener);
        document.addEventListener('keydown', this.keydownListener);
    }

    disconnect() {
        this.cleanups?.forEach((cleanup) => cleanup());
        this.cleanups = [];

        if (this.documentClickListener) {
            document.removeEventListener('click', this.documentClickListener);
        }

        if (this.keydownListener) {
            document.removeEventListener('keydown', this.keydownListener);
        }
    }

    toggle(event, button) {
        event.preventDefault();
        event.stopPropagation();

        const wrapper = button.closest('.saved-payment-method__menu-wrapper');
        const menu = wrapper?.querySelector('[data-payment-menu]');

        if (!menu) {
            return;
        }

        const willOpen = menu.hidden;

        this.closeAll(button);
        menu.hidden = !willOpen;
        button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
    }

    closeAll(exceptButton = null) {
        this.menuButtons.forEach((button) => {
            if (button === exceptButton) {
                return;
            }

            const wrapper = button.closest('.saved-payment-method__menu-wrapper');
            const menu = wrapper?.querySelector('[data-payment-menu]');

            if (!menu) {
                return;
            }

            menu.hidden = true;
            button.setAttribute('aria-expanded', 'false');
        });
    }
}
