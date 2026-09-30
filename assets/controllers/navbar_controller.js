import { Controller } from '@hotwired/stimulus';
import Mmenu from 'mmenu-js';

export default class extends Controller {
    static targets = ['button', 'menu'];

    connect() {
        if (!this.hasButtonTarget || !this.hasMenuTarget) {
            return;
        }

        const menu = new Mmenu(this.menuTarget, {
            slidingSubmenus: false,

            offCanvas: {
                position: 'right-front',
            },

            theme: 'light',
        });

        this.api = menu.API;

        this.handleOpen = () => this.api.open();
        this.handleOpenAfter = () => this.buttonTarget.setAttribute('aria-expanded', 'true');
        this.handleCloseAfter = () => this.buttonTarget.setAttribute('aria-expanded', 'false');

        this.buttonTarget.addEventListener('click', this.handleOpen);
        this.api.bind('open:after', this.handleOpenAfter);
        this.api.bind('close:after', this.handleCloseAfter);
    }

    disconnect() {
        if (this.hasButtonTarget && this.handleOpen) {
            this.buttonTarget.removeEventListener('click', this.handleOpen);
        }
    }
}
