import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        url: String,
        token: String,
        storageKey: {
            type: String,
            default: 'browser_preferences_saved',
        },
    };

    connect() {
        if (localStorage.getItem(this.storageKeyValue) === '1') {
            return;
        }

        const preferences = this.getBrowserPreferences();

        fetch(this.urlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                _token: this.tokenValue,
                ...preferences,
            }),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success === true) {
                    localStorage.setItem(this.storageKeyValue, '1');
                }
            })
            .catch((error) => {
                console.error('Erreur détection navigateur :', error);
            });
    }

    getBrowserPreferences() {
        const locale = navigator.language || navigator.languages?.[0] || 'fr-FR';

        const language = locale.split('-')[0]?.toLowerCase() || null;
        const country = locale.split('-')[1]?.toUpperCase() || null;

        const timeZone = Intl.DateTimeFormat().resolvedOptions().timeZone || null;

        return {
            locale,
            language,
            country,
            timeZone,
        };
    }
}
