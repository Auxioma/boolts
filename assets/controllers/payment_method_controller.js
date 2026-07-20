import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'form',
        'element',
        'error',
        'submitButton',
        'cardholderName',
        'defaultCheckbox'
    ];

    static values = {
        publicKey: String,
        createUrl: String,
        completeUrl: String,
        csrf: String
    };

    stripe = null;
    elements = null;
    paymentElement = null;
    clientSecret = null;
    setupIntentId = null;

    async connect() {
        if (typeof window.Stripe !== 'function') {
            this.showError('La bibliothèque Stripe est introuvable.');
            return;
        }

        this.stripe = window.Stripe(this.publicKeyValue);

        await this.initializeSetupIntent();
    }

    async initializeSetupIntent() {
        try {
            this.setLoading(true);
            this.hideError();

            const response = await fetch(this.createUrlValue, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.csrfValue
                }
            });

            const data = await this.readJson(response);

            if (!response.ok || data.success === false) {
                throw new Error(
                    data.message || 'Impossible d’initialiser Stripe.'
                );
            }

            this.clientSecret = data.clientSecret;
            this.setupIntentId = data.setupIntentId;

            this.elements = this.stripe.elements({
                clientSecret: this.clientSecret,
                appearance: {
                    theme: 'stripe',
                    variables: {
                        borderRadius: '8px'
                    }
                }
            });

            this.paymentElement = this.elements.create('payment', {
                layout: 'tabs',
                paymentMethodOrder: ['card']
            });

            this.paymentElement.mount(this.elementTarget);
        } catch (error) {
            console.error(error);
            this.showError(error.message);
        } finally {
            this.setLoading(false);
        }
    }

    async submit(event) {
        event.preventDefault();

        if (!this.stripe || !this.elements || !this.clientSecret) {
            this.showError('Stripe n’est pas encore initialisé.');
            return;
        }

        const cardholderName = this.cardholderNameTarget.value.trim();

        if (!cardholderName) {
            this.showError('Veuillez renseigner le nom du titulaire.');
            return;
        }

        try {
            this.setLoading(true);
            this.hideError();

            /*
             * Vérifie d'abord que les champs Stripe sont complets.
             */
            const submitResult = await this.elements.submit();

            if (submitResult.error) {
                throw new Error(
                    submitResult.error.message
                    || 'Les informations de la carte sont incomplètes.'
                );
            }

            /*
             * Stripe gère automatiquement une éventuelle authentification
             * 3D Secure.
             */
            const {setupIntent, error} = await this.stripe.confirmSetup({
                elements: this.elements,
                clientSecret: this.clientSecret,
                confirmParams: {
                    payment_method_data: {
                        billing_details: {
                            name: cardholderName
                        }
                    },
                    return_url: window.location.href
                },
                redirect: 'if_required'
            });

            if (error) {
                throw new Error(
                    error.message || 'La carte n’a pas pu être enregistrée.'
                );
            }

            if (!setupIntent || setupIntent.status !== 'succeeded') {
                throw new Error(
                    'La validation de la carte n’est pas terminée.'
                );
            }

            await this.completePaymentMethod(setupIntent.id);
        } catch (error) {
            console.error(error);
            this.showError(error.message);
        } finally {
            this.setLoading(false);
        }
    }

    async completePaymentMethod(setupIntentId) {
        const response = await fetch(this.completeUrlValue, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': this.csrfValue
            },
            body: JSON.stringify({
                setupIntentId,
                setAsDefault: this.defaultCheckboxTarget.checked
            })
        });

        const data = await this.readJson(response);

        if (!response.ok || data.success === false) {
            throw new Error(
                data.message || 'Impossible d’enregistrer la carte.'
            );
        }

        window.dispatchEvent(new CustomEvent('payment-method:created', {
            detail: data.paymentMethod
        }));

        /*
         * Pour commencer simplement, recharge la liste des cartes.
         */
        window.location.reload();
    }

    async readJson(response) {
        const contentType = response.headers.get('content-type') || '';

        if (!contentType.includes('application/json')) {
            throw new Error(
                'Le serveur a retourné une réponse invalide.'
            );
        }

        return response.json();
    }

    setLoading(loading) {
        if (!this.hasSubmitButtonTarget) {
            return;
        }

        this.submitButtonTarget.disabled = loading;
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
}
