import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
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
    }

    openFromHash() {
        const target = window.location.hash.replace(/^#/, '');

        if (!target) {
            return;
        }

        this.activate(target);
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
        });

        this.contents.forEach((content) => {
            content.classList.add('d-none');
        });

        targetLink?.classList.add('active');
        targetElement.classList.remove('d-none');

        return true;
    }

    replaceHash(target) {
        if (!target || !window.history?.replaceState) {
            return;
        }

        const url = new URL(window.location.href);

        if (url.hash === `#${target}`) {
            return;
        }

        url.hash = target;
        window.history.replaceState(null, '', url);
    }
}
