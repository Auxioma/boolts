import { Controller } from '@hotwired/stimulus';

// iPhone en mode « Demander le site pour ordinateur » : UA Mac + écran tactile étroit → rendu desktop forcé
export default class extends Controller {
    static values = {
        width: { type: Number, default: 1440 },
    };

    connect() {
        if (!/Macintosh/.test(navigator.userAgent) || navigator.maxTouchPoints <= 1 || Math.min(screen.width, screen.height) >= 600) {
            return;
        }

        document.querySelector('meta[name="viewport"]')?.setAttribute('content', `width=${this.widthValue}`);
    }
}
