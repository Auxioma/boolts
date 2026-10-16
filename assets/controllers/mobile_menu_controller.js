import { Controller } from '@hotwired/stimulus';

/*
 * Menu mobile basé sur mmenu-light.
 *
 * mmenu-light n'enveloppe pas le <body> (contrairement à mmenu.js) : il ajoute
 * seulement un tiroir .mm-ocd en fin de body. Les modals en position: fixed
 * (Bootstrap .modal, .bt-media-modal, .payment-modal…) ne sont donc pas impactées.
 *
 * Installation : php bin/console importmap:require mmenu-light mmenu-light/dist/mmenu-light.css
 */
export default class extends Controller {
    static targets = ['menu', 'source', 'button'];
    static values = {
        mediaQuery: { type: String, default: '(max-width: 1199.98px)' },
        title: { type: String, default: 'Menu' },
    };

    async connect() {
        this.handleBeforeCache = () => this.teardown();
        this.handleKeydown = (event) => {
            if (event.key === 'Escape') {
                this.close();
            }
        };
        this.handleMenuClick = (event) => {
            const link = event.target.closest('a[href]');

            // Les liens qui ouvrent un sous-menu sont gérés par mmenu-light.
            if (link && !link.nextElementSibling?.matches('ul')) {
                this.close();
            }
        };

        document.addEventListener('turbo:before-cache', this.handleBeforeCache);
        document.addEventListener('keydown', this.handleKeydown);

        let MmenuLight;

        try {
            await import('mmenu-light/dist/mmenu-light.css');
            const module = await import('mmenu-light');
            MmenuLight = module.default ?? module.MmenuLight ?? window.MmenuLight;
        } catch (error) {
            console.warn('[mobile-menu] mmenu-light indisponible :', error);
            return;
        }

        // Le contrôleur a pu être déconnecté pendant le chargement (navigation Turbo).
        if (!this.element.isConnected || !MmenuLight || !this.hasMenuTarget) {
            return;
        }

        this.menuElement = this.menuTarget;
        this.sourceElement = this.sourceTarget;
        this.pristineMenu = this.menuElement.cloneNode(true);

        const menu = new MmenuLight(this.menuElement, this.mediaQueryValue);
        menu.navigation({ title: this.titleValue, theme: 'light', selected: 'is-active' });
        this.drawer = menu.offcanvas({ position: 'right' });

        // Le tiroir est hors du contrôleur : on écoute directement ses clics.
        this.menuElement.addEventListener('click', this.handleMenuClick);
        this.observeDrawer();
    }

    disconnect() {
        document.removeEventListener('turbo:before-cache', this.handleBeforeCache);
        document.removeEventListener('keydown', this.handleKeydown);
        this.teardown();
    }

    open(event) {
        event?.preventDefault();
        this.drawer?.open();
    }

    close() {
        this.drawer?.close();
    }

    observeDrawer() {
        this.drawerObserver = new MutationObserver(() => {
            const isOpen = this.drawer.wrapper.classList.contains('mm-ocd--open');

            if (this.hasButtonTarget) {
                this.buttonTarget.setAttribute('aria-expanded', String(isOpen));
            }
        });

        this.drawerObserver.observe(this.drawer.wrapper, { attributes: true, attributeFilter: ['class'] });
    }

    // Remet le menu dans le header et supprime le tiroir : snapshot Turbo propre, aucun résidu sur <body>.
    teardown() {
        if (!this.drawer) {
            return;
        }

        this.drawer.close();
        this.drawerObserver?.disconnect();
        this.menuElement.removeEventListener('click', this.handleMenuClick);

        // Version d'origine (sans les classes mm-spn) pour une réinitialisation propre.
        this.sourceElement.replaceChildren(this.pristineMenu);
        this.menuElement.remove();

        this.drawer.wrapper.remove();
        document.body.classList.remove('mm-ocd-opened');

        if (this.hasButtonTarget) {
            this.buttonTarget.setAttribute('aria-expanded', 'false');
        }

        this.drawer = null;
    }
}
