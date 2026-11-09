import { Controller } from '@hotwired/stimulus';

/* stimulusFetch: 'lazy' */
export default class extends Controller {
    static targets = ['input', 'button', 'icon'];

    toggle() {
        const isHidden = this.inputTarget.type === 'password';

        this.inputTarget.type = isHidden ? 'text' : 'password';
        this.iconTarget.classList.toggle('icon-eye', !isHidden);
        this.iconTarget.classList.toggle('icon-eye-closed', isHidden);
        this.buttonTarget.setAttribute('aria-label', isHidden ? 'Masquer le mot de passe' : 'Afficher le mot de passe');
    }
}
