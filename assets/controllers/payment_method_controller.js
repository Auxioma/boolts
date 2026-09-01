import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'modal',
        'cardNumber',
        'cardExpiry',
        'cardCvc',
        'country',
        'error',
        'success',
        'submitButton',
        'cardholderName',
        'defaultCheckbox'
    ];

    static values = {
        publicKey: String,
        createUrl: String,
        completeUrl: String,
        csrf: String,
        returnHash: String
    };

    stripe = null;
    stripePromise = null;
    elements = null;
    cardNumberElement = null;
    cardExpiryElement = null;
    cardCvcElement = null;
    clientSecret = null;
    setupIntentId = null;
    initialized = false;
    initializing = false;

    elementsReady = {
        cardNumber: false,
        cardExpiry: false,
        cardCvc: false
    };

    connect() {
        this.hideError();
        this.hideSuccess();

        if (!this.publicKeyValue || !this.publicKeyValue.startsWith('pk_')) {
            this.showError('La clé publique Stripe est absente ou invalide.');
            return;
        }

        // Stripe.js est chargé en bas de page : il peut ne pas être encore
        // disponible quand Stimulus connecte le contrôleur. On lance le
        // préchargement sans bloquer ; ensureStripe() ré-attend au besoin.
        this.ensureStripe().catch(() => {
            // L’erreur éventuelle sera affichée à l’ouverture de la modale.
        });
    }

    /**
     * Garantit que window.Stripe est disponible et que this.stripe est
     * instancié. Résout avec l’instance Stripe, rejette si le script ne
     * peut pas être chargé.
     */
    ensureStripe() {
        if (this.stripe) {
            return Promise.resolve(this.stripe);
        }

        if (!this.stripePromise) {
            this.stripePromise = this.loadStripeJs()
                .then(() => {
                    if (typeof window.Stripe !== 'function') {
                        throw new Error(
                            'Stripe.js n’est pas chargé dans la page.'
                        );
                    }

                    this.stripe = window.Stripe(this.publicKeyValue);

                    return this.stripe;
                })
                .catch((error) => {
                    this.stripePromise = null;
                    throw error;
                });
        }

        return this.stripePromise;
    }

    loadStripeJs() {
        if (typeof window.Stripe === 'function') {
            return Promise.resolve();
        }

        const existing = document.querySelector(
            'script[src^="https://js.stripe.com/v3"]'
        );

        if (existing) {
            return this.waitForScript(existing);
        }

        return new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = 'https://js.stripe.com/v3/';
            script.async = true;
            script.addEventListener('load', () => resolve(), { once: true });
            script.addEventListener(
                'error',
                () => reject(new Error('Impossible de charger Stripe.js.')),
                { once: true }
            );
            document.head.appendChild(script);
        });
    }

    waitForScript(script) {
        return new Promise((resolve, reject) => {
            if (typeof window.Stripe === 'function') {
                resolve();
                return;
            }

            const timeout = window.setTimeout(() => {
                reject(new Error('Le chargement de Stripe.js a expiré.'));
            }, 10000);

            script.addEventListener('load', () => {
                window.clearTimeout(timeout);
                resolve();
            }, { once: true });

            script.addEventListener('error', () => {
                window.clearTimeout(timeout);
                reject(new Error('Impossible de charger Stripe.js.'));
            }, { once: true });
        });
    }

    async open() {
        this.modalTarget.classList.add('is-open');
        document.body.style.overflow = 'hidden';

        if (!this.initialized && !this.initializing) {
            await this.initializeSetupIntent();
        }
    }

    close() {
        this.modalTarget.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    closeFromOverlay(event) {
        if (event.target === this.modalTarget) {
            this.close();
        }
    }

    closeFromKeyboard(event) {
        if (event.key === 'Escape') {
            this.close();
        }
    }

    cardholderChanged() {
        this.hideError();
    }

    countryChanged() {
        this.hideError();
    }

    async initializeSetupIntent() {
        this.initializing = true;

        try {
            this.setLoading(true);
            this.hideError();

            await this.ensureStripe();

            const response = await fetch(this.createUrlValue, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfValue
                },
                credentials: 'same-origin'
            });

            const rawResponse = await response.text();

            let data;

            try {
                data = JSON.parse(rawResponse);
            } catch {
                throw new Error(
                    `Le serveur a retourné une réponse non JSON (${response.status}).`
                );
            }

            if (!response.ok || data.success !== true) {
                throw new Error(
                    data.message
                    || `Impossible d’initialiser Stripe (${response.status}).`
                );
            }

            if (!data.clientSecret || !data.setupIntentId) {
                throw new Error(
                    'Le serveur n’a pas retourné les informations Stripe attendues.'
                );
            }

            this.clientSecret = data.clientSecret;
            this.setupIntentId = data.setupIntentId;

            this.elements = this.stripe.elements();

            const style = {
                base: {
                    fontSize: '16px',
                    color: '#0A1633',
                    fontFamily: '"Inter", -apple-system, BlinkMacSystemFont, sans-serif',
                    '::placeholder': {
                        color: '#9AA3B2'
                    }
                },
                invalid: {
                    color: '#DC3545',
                    iconColor: '#DC3545'
                }
            };

            this.cardNumberElement = this.elements.create('cardNumber', {
                style,
                showIcon: true
            });

            this.cardExpiryElement = this.elements.create('cardExpiry', {
                style
            });

            this.cardCvcElement = this.elements.create('cardCvc', {
                style
            });

            this.bindElementEvents(this.cardNumberElement, 'cardNumber');
            this.bindElementEvents(this.cardExpiryElement, 'cardExpiry');
            this.bindElementEvents(this.cardCvcElement, 'cardCvc');

            this.cardNumberElement.mount(this.cardNumberTarget);
            this.cardExpiryElement.mount(this.cardExpiryTarget);
            this.cardCvcElement.mount(this.cardCvcTarget);
        } catch (error) {
            console.error('Erreur Stripe :', error);

            this.initializing = false;
            this.setLoading(false);
            this.showError(
                error instanceof Error
                    ? error.message
                    : 'Impossible d’initialiser Stripe.'
            );
        }
    }

    bindElementEvents(element, name) {
        element.on('ready', () => {
            this.elementsReady[name] = true;

            const allReady = Object.values(this.elementsReady)
                .every(ready => ready === true);

            if (allReady) {
                this.initialized = true;
                this.initializing = false;
                this.setLoading(false);
            }
        });

        element.on('loaderror', event => {
            this.initializing = false;
            this.setLoading(false);
            this.showError(
                event?.error?.message
                || 'Le formulaire Stripe ne peut pas être chargé.'
            );
        });

        element.on('change', event => {
            if (event.error) {
                this.showError(event.error.message);
            } else {
                this.hideError();
            }
        });
    }

    async submit(event) {
        event.preventDefault();

        this.hideError();
        this.hideSuccess();

        if (!this.stripe || !this.initialized || !this.clientSecret) {
            this.showError('Stripe n’est pas encore prêt.');
            return;
        }

        const cardholderName = this.cardholderNameTarget.value.trim();

        if (cardholderName.length < 2) {
            this.showError('Veuillez renseigner le nom du titulaire.');
            this.cardholderNameTarget.focus();
            return;
        }

        const country = this.hasCountryTarget
            ? this.countryTarget.value
            : '';

        if (!country) {
            this.showError('Veuillez sélectionner le pays.');

            if (this.hasCountryTarget) {
                this.countryTarget.focus();
            }

            return;
        }

        try {
            this.setLoading(true);

            const result = await this.stripe.confirmCardSetup(
                this.clientSecret,
                {
                    payment_method: {
                        card: this.cardNumberElement,
                        billing_details: {
                            name: cardholderName,
                            address: {
                                country
                            }
                        }
                    }
                }
            );

            if (result.error) {
                throw new Error(result.error.message);
            }

            if (!result.setupIntent) {
                throw new Error('Stripe n’a retourné aucun SetupIntent.');
            }

            if (result.setupIntent.status !== 'succeeded') {
                throw new Error(
                    `La carte n’est pas validée. Statut : ${result.setupIntent.status}.`
                );
            }

            await this.complete(result.setupIntent.id);
        } catch (error) {
            console.error('Erreur de validation Stripe :', error);

            this.setLoading(false);
            this.showError(
                error instanceof Error
                    ? error.message
                    : 'Impossible d’enregistrer la carte.'
            );
        }
    }

    async complete(setupIntentId) {
        const response = await fetch(this.completeUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfValue
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                setupIntentId,
                setAsDefault: this.defaultCheckboxTarget.checked
            })
        });

        const rawResponse = await response.text();

        let data;

        try {
            data = JSON.parse(rawResponse);
        } catch {
            throw new Error(
                `Le serveur a retourné une réponse non JSON (${response.status}).`
            );
        }

        if (!response.ok || data.success !== true) {
            throw new Error(
                data.message
                || 'Le moyen de paiement n’a pas pu être enregistré.'
            );
        }

        this.showSuccess(data.message);
        this.setLoading(false);

        window.setTimeout(() => {
            this.reloadWithReturnHash();
        }, 700);
    }

    async delete(event) {
        event.preventDefault();

        const button = event.currentTarget;
        const deleteUrl = button.dataset.paymentUrl;
        const cardLast4 = button.dataset.cardLast4;

        if (!deleteUrl) {
            this.showPageError('La route de suppression est introuvable.');
            return;
        }

        const confirmed = window.confirm(
            cardLast4
                ? `Supprimer la carte se terminant par ${cardLast4} ?`
                : 'Supprimer cette carte bancaire ?'
        );

        if (!confirmed) {
            return;
        }

        try {
            button.disabled = true;

            const response = await fetch(deleteUrl, {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfValue
                },
                credentials: 'same-origin'
            });

            const data = await this.readJsonResponse(response);

            if (!response.ok || data.success !== true) {
                throw new Error(
                    data.message
                    || 'Le moyen de paiement n’a pas pu être supprimé.'
                );
            }

            this.showPageSuccess(data.message);

            window.setTimeout(() => {
                this.reloadWithReturnHash();
            }, 500);
        } catch (error) {
            console.error('Erreur de suppression Stripe :', error);
            button.disabled = false;
            this.showPageError(
                error instanceof Error
                    ? error.message
                    : 'Impossible de supprimer la carte.'
            );
        }
    }

    async readJsonResponse(response) {
        const rawResponse = await response.text();

        try {
            return JSON.parse(rawResponse);
        } catch {
            throw new Error(
                `Le serveur a retourné une réponse non JSON (${response.status}).`
            );
        }
    }

    reloadWithReturnHash() {
        this.replaceReturnHash();
        window.location.reload();
    }

    replaceReturnHash() {
        if (
            !this.hasReturnHashValue
            || !this.returnHashValue
            || !window.history?.replaceState
        ) {
            return;
        }

        const url = new URL(window.location.href);
        const returnHash = this.returnHashValue.startsWith('#')
            ? this.returnHashValue
            : `#${this.returnHashValue}`;

        url.hash = returnHash;
        window.history.replaceState(null, '', url);
    }

    setLoading(loading) {
        if (!this.hasSubmitButtonTarget) {
            return;
        }

        this.submitButtonTarget.disabled = loading || !this.initialized;
        this.submitButtonTarget.textContent = loading
            ? 'Traitement…'
            : 'Ajouter';
    }

    showError(message) {
        if (!this.hasErrorTarget) {
            return;
        }

        this.errorTarget.textContent = message;
        this.errorTarget.classList.remove('d-none');
    }

    hideError() {
        if (!this.hasErrorTarget) {
            return;
        }

        this.errorTarget.textContent = '';
        this.errorTarget.classList.add('d-none');
    }

    showSuccess(message) {
        if (!this.hasSuccessTarget) {
            return;
        }

        this.successTarget.textContent = message;
        this.successTarget.classList.remove('d-none');
    }

    hideSuccess() {
        if (!this.hasSuccessTarget) {
            return;
        }

        this.successTarget.textContent = '';
        this.successTarget.classList.add('d-none');
    }

    showPageSuccess(message) {
        this.showPageMessage('.js-success-message', message);
    }

    showPageError(message) {
        if (!this.showPageMessage('.js-error-message', message)) {
            this.showError(message);
        }
    }

    showPageMessage(selector, message) {
        const element = document.querySelector(selector);

        if (!element) {
            return false;
        }

        element.textContent = message;
        element.classList.remove('d-none');

        window.clearTimeout(element.hideTimeout);

        element.hideTimeout = window.setTimeout(() => {
            element.classList.add('d-none');
            element.textContent = '';
        }, 3000);

        return true;
    }
}
