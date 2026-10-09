import { Controller } from '@hotwired/stimulus';
import Splide from '@splidejs/splide';

/*
 * Slider des photos du bien sur mobile (< 768px).
 * Template : templates/public/detail_bien/_partials/hero_slider_mobile.html.twig
 * Monté uniquement sous 768px, détruit au-dessus.
 */
export default class extends Controller {
    static targets = ['slider', 'counter'];

    connect() {
        this.mediaQuery = window.matchMedia('(max-width: 767px)');
        this.handleMediaChange = () => this.toggle();
        this.mediaQuery.addEventListener('change', this.handleMediaChange);
        this.toggle();
    }

    disconnect() {
        this.mediaQuery.removeEventListener('change', this.handleMediaChange);
        this.destroy();
    }

    toggle() {
        if (this.mediaQuery.matches) {
            this.mount();
        } else {
            this.destroy();
        }
    }

    mount() {
        if (this.splide) {
            return;
        }

        this.splide = new Splide(this.sliderTarget, {
            type: 'slide',
            perPage: 1,
            arrows: false,
            pagination: true,
            drag: true,
            speed: 400,
            rewind: false,
            classes: {
                pagination: 'splide__pagination bt-hero-slider__pagination',
                page: 'splide__pagination__page bt-hero-slider__dot',
            },
        });

        // Un clic sur un dot ne doit pas ouvrir la galerie ([data-open-gallery] sur la section)
        this.splide.on('pagination:mounted', (data) => {
            data.list.addEventListener('click', (event) => event.stopPropagation());
        });

        this.splide.on('mounted move', () => this.updateCounter());

        this.splide.mount();
    }

    destroy() {
        if (!this.splide) {
            return;
        }

        this.splide.destroy();
        this.splide = null;
    }

    updateCounter() {
        if (!this.hasCounterTarget || !this.splide) {
            return;
        }

        this.counterTarget.textContent = `${this.splide.index + 1}/${this.splide.length}`;
    }
}
