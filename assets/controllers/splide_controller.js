import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';

export default class extends Controller {
    static targets = ['slider', 'previous', 'next'];

    connect() {
        if (this.splide) {
            return;
        }

        this.splide = new Splide(this.sliderTarget, {
            type: 'slide',
            fixedWidth: '315px',
            perMove: 1,
            gap: '20px',

            arrows: false,
            pagination: false,

            drag: true,
            autoplay: false,
            speed: 800,
            rewind: false,
            trimSpace: false,

            breakpoints: {
                1200: {
                    fixedWidth: '330px',
                },
                992: {
                    fixedWidth: '270px',
                },
                768: {
                    fixedWidth: '245px',
                    padding: {
                        right: '55px',
                    },
                },
                576: {
                    fixedWidth: '225px',
                    gap: '16px',
                    padding: {
                        right: '45px',
                    },
                },
            },
        });

        this.splide.on('mounted moved updated resized refreshed', () => {
            this.updateButtons();
        });

        this.splide.mount();
        this.updateButtons();
    }

    previous() {
        if (!this.splide) {
            return;
        }

        this.splide.go('<');
    }

    next() {
        if (!this.splide) {
            return;
        }

        this.splide.go('>');
    }

    updateButtons() {
        if (!this.splide) {
            return;
        }

        const currentIndex = this.splide.index;
        const endIndex = this.splide.Components.Controller.getEnd();

        if (this.hasPreviousTarget) {
            this.previousTarget.disabled = currentIndex <= 0;
        }

        if (this.hasNextTarget) {
            this.nextTarget.disabled = currentIndex >= endIndex;
        }
    }

    disconnect() {
        if (!this.splide) {
            return;
        }

        this.splide.destroy();
        this.splide = null;
    }
}
