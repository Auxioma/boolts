import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        statusUrl: String,
        toggleUrl: String
    };

    connect() {
        this.toggle = this.element.querySelector('.js-two-factor-switch');
        this.content = this.element.querySelector('.js-two-factor-content');

        if (!this.toggle || !this.content) {
            return;
        }

        this.toggleListener = () => {
            this.toggleTwoFactor();
        };

        this.loadStatus();
        this.toggle.addEventListener('change', this.toggleListener);
    }

    disconnect() {
        if (this.toggle && this.toggleListener) {
            this.toggle.removeEventListener('change', this.toggleListener);
        }
    }

    async loadStatus() {
        try {
            const response = await fetch(this.statusUrlValue, {
                credentials: 'same-origin'
            });

            if (!response.ok) {
                throw response;
            }

            const data = await response.json();
            this.refreshUI(data.enabled);
        } catch {
        }
    }

    async toggleTwoFactor() {
        const desired = this.toggle.checked;

        try {
            const response = await fetch(this.toggleUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                credentials: 'same-origin',
                body: JSON.stringify({ enabled: desired })
            });

            const text = await response.text();
            let data;

            try {
                data = JSON.parse(text);
            } catch {
                data = text;
            }

            if (!response.ok) {
                console.error('Toggle 2FA error', response.status, data);
                this.toggle.checked = !desired;
                return;
            }

            if (data && typeof data.enabled !== 'undefined') {
                this.refreshUI(data.enabled);
            } else {
                this.toggle.checked = !desired;
            }
        } catch {
            this.toggle.checked = !desired;
        }
    }

    refreshUI(enabled) {
        this.toggle.checked = Boolean(enabled);
        this.content.classList.toggle('d-none', !enabled);
    }
}
