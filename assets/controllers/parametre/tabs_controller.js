import { Controller } from '@hotwired/stimulus';

/*
 * Navigation en étapes des paramètres :
 * - sans hash : liste des sous-pages (aucun bouton « Retour ») ;
 * - avec un hash connu (#cat1…) : contenu de la sous-page seul,
 *   avec un bouton « Retour » vers la liste.
 * L'état ne dépend que du hash, il est donc identique quelle que soit
 * la largeur d'écran et survit au rechargement ou à un accès direct.
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['list', 'content', 'back'];

    connect() {
        this.links = [...this.element.querySelectorAll('.js-tab-link')];
        this.contents = [...this.element.querySelectorAll('.js-tab-content')];
        this.cleanups = [];

        this.links.forEach((link) => {
            const listener = (event) => {
                this.open(event, link);
            };

            link.addEventListener('click', listener);
            this.cleanups.push(() => link.removeEventListener('click', listener));
        });

        const hashListener = () => {
            this.openFromHash();
        };

        window.addEventListener('hashchange', hashListener);
        this.cleanups.push(() => window.removeEventListener('hashchange', hashListener));

        this.openFromHash();
    }

    disconnect() {
        this.cleanups?.forEach((cleanup) => cleanup());
        this.cleanups = [];
    }

    open(event, link) {
        event.preventDefault();

        const target = link.dataset.tab;

        if (!this.activate(target, link)) {
            return;
        }

        this.replaceHash(target);
        this.focusContentTitle(target);
    }

    back(event) {
        event.preventDefault();

        const activeLink = this.links.find((item) => item.classList.contains('active'));

        this.showList();
        this.replaceHash(null);

        activeLink?.focus();
    }

    openFromHash() {
        const target = window.location.hash.replace(/^#/, '');

        if (!target || !this.activate(target)) {
            this.showList();
        }
    }

    activate(target, link = null) {
        const targetElement = target
            ? this.contents.find((content) => content.id === target)
            : null;

        if (!targetElement) {
            return false;
        }

        const targetLink = link
            || this.links.find((item) => item.dataset.tab === target);

        this.links.forEach((item) => {
            item.classList.remove('active');
            item.removeAttribute('aria-current');
        });

        this.contents.forEach((content) => {
            content.classList.add('d-none');
        });

        targetLink?.classList.add('active');
        targetLink?.setAttribute('aria-current', 'page');
        targetElement.classList.remove('d-none');

        this.showContent();

        return true;
    }

    showList() {
        this.contentTarget.classList.add('d-none');
        this.listTarget.classList.remove('d-none');
    }

    showContent() {
        this.listTarget.classList.add('d-none');
        this.contentTarget.classList.remove('d-none');
    }

    // Place le focus sur le titre de la sous-page ouverte pour les
    // utilisateurs clavier et les lecteurs d'écran.
    focusContentTitle(target) {
        const title = this.contents
            .find((content) => content.id === target)
            ?.querySelector('h2, h3');

        if (!title) {
            this.backTarget.focus();
            return;
        }

        title.setAttribute('tabindex', '-1');
        title.focus({ preventScroll: true });
        window.scrollTo({ top: 0 });
    }

    replaceHash(target) {
        if (!window.history?.replaceState) {
            return;
        }

        const url = new URL(window.location.href);
        const hash = target ? `#${target}` : '';

        if (url.hash === hash) {
            return;
        }

        url.hash = hash;
        // Conserve l'état de Turbo pour ne pas casser la navigation précédent/suivant.
        window.history.replaceState(window.history.state, '', url);
    }
}
