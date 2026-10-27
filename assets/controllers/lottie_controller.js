import { Controller } from '@hotwired/stimulus';

/*
 * Animation Lottie (fichier JSON exporté de Figma) jouée en boucle dans l'élément.
 * Usage : data-controller="lottie" data-lottie-path-value="{{ asset('…json') }}"
 */
/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static values = { path: String };

    async connect() {
        const { default: lottie } = await import('lottie-web');

        // Déconnecté pendant le chargement de la librairie
        if (!this.element.isConnected) {
            return;
        }

        this.animation = lottie.loadAnimation({
            container: this.element,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: this.pathValue,
        });
    }

    disconnect() {
        this.animation?.destroy();
        this.animation = null;
    }
}
