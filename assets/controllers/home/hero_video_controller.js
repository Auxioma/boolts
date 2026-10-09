import { Controller } from '@hotwired/stimulus';

/*
 * Vidéo de fond de la home (templates/public/home/index.html.twig).
 * Avec Turbo, la page est restaurée depuis le cache ou rendue sans rechargement :
 * l'attribut autoplay n'est alors pas toujours rejoué et la vidéo reste figée / grise.
 * On relance donc la lecture à chaque connexion du controller.
 */
export default class extends Controller {
    connect() {
        this.onCanPlay = () => this.play();
        this.element.addEventListener('canplay', this.onCanPlay);

        // Source jamais chargée (rendu Turbo) : on force le chargement
        if (this.element.readyState === HTMLMediaElement.HAVE_NOTHING && this.element.networkState !== HTMLMediaElement.NETWORK_LOADING) {
            this.element.load();
        }

        this.play();
    }

    disconnect() {
        this.element.removeEventListener('canplay', this.onCanPlay);
        this.element.pause();
    }

    play() {
        this.element.muted = true;

        const promise = this.element.play();

        if (promise) {
            // Autoplay refusé (mode économie d'énergie…) : le poster reste affiché
            promise.catch(() => {});
        }
    }
}
