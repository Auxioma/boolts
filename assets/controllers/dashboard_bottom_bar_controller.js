import { Controller } from '@hotwired/stimulus';

/*
 * Barre de navigation mobile du dashboard agence.
 * Les boutons « Paramètres » et « Plus » ouvrent chacun un menu affiché comme une page,
 * placé entre la topbar et la barre du bas, sans recouvrir ni l'une ni l'autre.
 * Un seul menu est ouvert à la fois.
 */
export default class extends Controller {
    static targets = ['panel', 'settingsButton', 'morePanel', 'moreButton'];

    connect() {
        this.handleKeydown = (event) => {
            if (event.key === 'Escape') {
                this.close();
            }
        };
        this.handleResize = () => {
            const panel = this.openPanel();

            if (panel) {
                this.position(panel);
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

    // Menu Paramètres : le clic ouvre seulement, un clic sur le bouton d'un menu déjà ouvert ne le ferme pas
    toggle() {
        if (!this.isOpen(this.panelTarget)) {
            this.open();
        }
    }

    open() {
        this.show(this.panelTarget, this.settingsButtonTarget);
    }

    // Menu Plus : le clic ouvre seulement, un clic sur le bouton d'un menu déjà ouvert ne le ferme pas
    toggleMore() {
        if (this.hasMorePanelTarget && !this.isOpen(this.morePanelTarget)) {
            this.show(this.morePanelTarget, this.moreButtonTarget);
        }
    }

    openMore() {
        if (this.hasMorePanelTarget) {
            this.show(this.morePanelTarget, this.moreButtonTarget);
        }
    }

    show(panel, button) {
        this.close();

        // La classe body d'abord : elle réaffiche la barre du bas masquée sur les pages de contenu Paramètres,
        // pour que position() mesure sa vraie hauteur
        document.body.classList.add('dashboard-settings-menu-open');
        this.position(panel);
        panel.hidden = false;
        button.setAttribute('aria-expanded', 'true');
        button.classList.add('is-open');
    }

    close() {
        this.panels().forEach(({ panel, button }) => {
            panel.hidden = true;
            button?.setAttribute('aria-expanded', 'false');
            button?.classList.remove('is-open');
        });

        document.body.classList.remove('dashboard-settings-menu-open');
    }

    // Lien du menu Paramètres : déjà sur la page Paramètres, on change d'onglet sans passer par Turbo
    // (Turbo gère mal la navigation vers la même page avec seulement une ancre différente : il fallait deux clics)
    navigate(event) {
        const link = event.currentTarget;
        const url = new URL(link.href, window.location.href);

        this.close();

        if (url.pathname !== window.location.pathname || !url.hash) {
            return;
        }

        event.preventDefault();

        const oldURL = window.location.href;

        if (url.hash !== window.location.hash) {
            window.history.replaceState(window.history.state, '', url);
        }

        window.dispatchEvent(new HashChangeEvent('hashchange', { oldURL, newURL: url.href }));
        window.scrollTo(0, 0);
    }

    panels() {
        const panels = [];

        if (this.hasPanelTarget) {
            panels.push({ panel: this.panelTarget, button: this.hasSettingsButtonTarget ? this.settingsButtonTarget : null });
        }

        if (this.hasMorePanelTarget) {
            panels.push({ panel: this.morePanelTarget, button: this.hasMoreButtonTarget ? this.moreButtonTarget : null });
        }

        return panels;
    }

    openPanel() {
        return this.panels().map(({ panel }) => panel).find((panel) => this.isOpen(panel)) || null;
    }

    isOpen(panel) {
        return Boolean(panel) && !panel.hidden;
    }

    // Le menu commence sous la topbar et s'arrête au-dessus de la barre du bas
    position(panel) {
        const topbar = document.querySelector('.topbar');
        const bottomBar = this.element.querySelector('.dashboard-bottom-bar');
        const top = topbar ? Math.max(topbar.getBoundingClientRect().bottom, 0) : 0;
        // La barre flotte au-dessus du bas de l'écran : on s'arrête à son bord haut
        const bottom = bottomBar ? Math.max(window.innerHeight - bottomBar.getBoundingClientRect().top, 0) : 0;

        panel.style.top = `${Math.ceil(top)}px`;
        panel.style.bottom = `${Math.ceil(bottom)}px`;
    }
}
