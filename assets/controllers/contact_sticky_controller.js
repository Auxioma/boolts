import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['aside'];

    static values = {
        breakpoint: {
            type: Number,
            default: 992,
        },
    };

    connect() {
        this.onScroll = this.update.bind(this);
        this.onResize = this.update.bind(this);

        this.rafId = null;

        window.addEventListener('scroll', this.onScroll, {
            passive: true,
        });

        window.addEventListener('resize', this.onResize);

        this.resizeObserver = new ResizeObserver(() => {
            this.requestUpdate();
        });

        this.resizeObserver.observe(this.asideTarget);

        this.requestUpdate();
    }

    disconnect() {
        window.removeEventListener('scroll', this.onScroll);
        window.removeEventListener('resize', this.onResize);

        if (this.resizeObserver) {
            this.resizeObserver.disconnect();
        }

        if (this.rafId) {
            window.cancelAnimationFrame(this.rafId);
        }

        this.reset();
    }

    update() {
        this.requestUpdate();
    }

    requestUpdate() {
        if (this.rafId) {
            return;
        }

        this.rafId = window.requestAnimationFrame(() => {
            this.rafId = null;
            this.calculatePosition();
        });
    }

    calculatePosition() {
        if (window.innerWidth < this.breakpointValue) {
            this.reset();
            return;
        }

        const navbar = document.querySelector(
            '.navbar, .bt-navbar, header'
        );

        const similarProperties = document.querySelector(
            '#similar-properties'
        );

        if (!similarProperties) {
            this.reset();
            return;
        }

        const navbarHeight = navbar
            ? navbar.getBoundingClientRect().height
            : 0;

        const topGap = 20;
        const bottomGap = 20;

        const fixedTop = navbarHeight + topGap;

        const wrapperRect = this.element.getBoundingClientRect();
        const asideRect = this.asideTarget.getBoundingClientRect();
        const similarRect = similarProperties.getBoundingClientRect();

        const availableViewportHeight =
            window.innerHeight - fixedTop - bottomGap;

        this.asideTarget.style.maxHeight = `${Math.max(
            availableViewportHeight,
            200
        )}px`;

        this.asideTarget.style.overflowY = 'auto';

        const stopPosition =
            similarRect.top - asideRect.height - bottomGap;

        /*
         * Position normale :
         * le bloc n'est pas encore arrivé sous la navbar.
         */
        if (wrapperRect.top >= fixedTop) {
            this.setNormal();
            return;
        }

        /*
         * Position bloquée :
         * le bloc approche de la section des biens similaires.
         */
        if (stopPosition <= fixedTop) {
            const translateY =
                similarProperties.offsetTop -
                this.element.offsetTop -
                asideRect.height -
                bottomGap;

            this.setStopped(Math.max(translateY, 0));
            return;
        }

        /*
         * Position fixe :
         * le bloc suit le scroll sous la navbar.
         */
        this.setFixed(fixedTop);
    }

    setNormal() {
        this.asideTarget.classList.remove(
            'is-fixed',
            'is-stopped'
        );

        this.asideTarget.style.position = '';
        this.asideTarget.style.top = '';
        this.asideTarget.style.left = '';
        this.asideTarget.style.width = '';
        this.asideTarget.style.transform = '';
    }

    setFixed(top) {
        const wrapperRect = this.element.getBoundingClientRect();

        this.asideTarget.classList.add('is-fixed');
        this.asideTarget.classList.remove('is-stopped');

        this.asideTarget.style.position = 'fixed';
        this.asideTarget.style.top = `${top}px`;
        this.asideTarget.style.left = `${wrapperRect.left}px`;
        this.asideTarget.style.width = `${wrapperRect.width}px`;
        this.asideTarget.style.transform = '';
    }

    setStopped(translateY) {
        this.asideTarget.classList.remove('is-fixed');
        this.asideTarget.classList.add('is-stopped');

        this.asideTarget.style.position = 'absolute';
        this.asideTarget.style.top = '0';
        this.asideTarget.style.left = '0';
        this.asideTarget.style.width = '100%';
        this.asideTarget.style.transform =
            `translateY(${translateY}px)`;
    }

    reset() {
        this.asideTarget.classList.remove(
            'is-fixed',
            'is-stopped'
        );

        this.asideTarget.style.position = '';
        this.asideTarget.style.top = '';
        this.asideTarget.style.left = '';
        this.asideTarget.style.width = '';
        this.asideTarget.style.transform = '';
        this.asideTarget.style.maxHeight = '';
        this.asideTarget.style.overflowY = '';
    }
}