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
    }

    disconnect() {
        this.cleanups?.forEach((cleanup) => cleanup());
        this.cleanups = [];
    }

    open(event, link) {
        event.preventDefault();

        const target = link.dataset.tab;
        const targetElement = target ? this.element.querySelector(`#${target}`) : null;

        if (!targetElement) {
            return;
        }

        this.links.forEach((item) => {
            item.classList.remove('active');
        });

        this.contents.forEach((content) => {
            content.classList.add('d-none');
        });

        link.classList.add('active');
        targetElement.classList.remove('d-none');
    }
}
