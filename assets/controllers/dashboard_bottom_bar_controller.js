import { Controller } from '@hotwired/stimulus';

/*
 * Barre de navigation mobile du dashboard agence.
 * Le bouton « Paramètres » ouvre un menu placé entre la topbar et la barre du bas,
 * sans recouvrir ni l'une ni l'autre.
 */
export default class extends Controller {
    static targets = ['panel', 'settingsButton'];

    connect() {
        this.handleKeydown = (event) => {
            if (event.key === 'Escape') {
                this.close();
            }
        };
        this.handleResize = () => {
            if (this.isOpen()) {
                this.position();
            }
        };

        document.addEventListener('keydown', this.handleKeydown);
        window.addEventListener('resize', this.handleResize);
    }

    disconnect() {
        document.removeEventListener('keydown', this.handleKeydown);
        window.removeEventListener('resize', this.handleResize);
        this.close();
    }

    toggle() {
        this.isOpen() ? this.close() : this.open();
    }

    open() {
        this.position();
        this.panelTarget.hidden = false;
        this.settingsButtonTarget.setAttribute('aria-expanded', 'true');
        this.settingsButtonTarget.classList.add('is-open');
        document.body.classList.add('dashboard-settings-menu-open');
    }

    close() {
        if (!this.hasPanelTarget) {
            return;
        }

        this.panelTarget.hidden = true;
        this.settingsButtonTarget.setAttribute('aria-expanded', 'false');
        this.settingsButtonTarget.classList.remove('is-open');
        document.body.classList.remove('dashboard-settings-menu-open');
    }

    isOpen() {
        return this.hasPanelTarget && !this.panelTarget.hidden;
    }

    // Le menu commence sous la topbar et s'arrête au-dessus de la barre du bas
    position() {
        const topbar = document.querySelector('.topbar');
        const bottomBar = this.element.querySelector('.dashboard-bottom-bar');
        const top = topbar ? Math.max(topbar.getBoundingClientRect().bottom, 0) : 0;
        const bottom = bottomBar ? bottomBar.getBoundingClientRect().height : 0;

        this.panelTarget.style.top = `${Math.ceil(top)}px`;
        this.panelTarget.style.bottom = `${Math.ceil(bottom)}px`;
    }
}
