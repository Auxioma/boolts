import { Controller } from '@hotwired/stimulus';

/*
 * Dernière étape d'inscription : transmet au serveur la langue et le fuseau
 * horaire du navigateur pour renseigner langue parlée / devise / fuseau horaire.
 *
 * - dépose un cookie `boolts_locale_hints` lu lors de la soumission finale ;
 * - envoie un beacon immédiat pour une prise en compte anticipée.
 */
export default class extends Controller {
    static values = {
        url: String,
        token: String,
    };

    connect() {
        const hints = this.collectHints();

        this.writeCookie(hints);
        this.sendBeacon(hints);
    }

    collectHints() {
        const locale =
            navigator.language || navigator.languages?.[0] || 'fr-FR';

        let timeZone = null;

        try {
            timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || null;
        } catch {
            timeZone = null;
        }

        return {
            locale,
            language: locale.split('-')[0]?.toLowerCase() || null,
            timeZone,
        };
    }

    writeCookie(hints) {
        const value = encodeURIComponent(JSON.stringify(hints));

        document.cookie =
            `boolts_locale_hints=${value}; path=/; max-age=1800; samesite=lax`;
    }

    sendBeacon(hints) {
        if (!this.hasUrlValue) {
            return;
        }

        fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                _token: this.tokenValue,
                ...hints,
            }),
        }).catch((error) => {
            console.error('Détection locale inscription :', error);
        });
    }
}
