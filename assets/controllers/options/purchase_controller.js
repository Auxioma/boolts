import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = [
        'paymentBlock',
        'paymentOption',
        'paymentMethod',
        'showPaymentMethodsButton',
        'selectedCardName',
        'selectedCardExpiration',
        'subscribeButton',
        'subscriptionMessage'
    ];

    static values = {
        subscriptionUrl: String,
        subscriptionCsrf: String,
        planPriceId: Number,
        redirectUrl: String,
        defaultPaymentMethodId: String
    };

    connect() {
        this.selectedPaymentMethodId = this.defaultPaymentMethodIdValue || null;
        this.subscribeButtonLabel = this.hasSubscribeButtonTarget
            ? this.subscribeButtonTarget.textContent.trim()
            : 'S’abonner';
        this.redirectTimeout = null;
    }

    disconnect() {
        if (this.redirectTimeout) {
            window.clearTimeout(this.redirectTimeout);
        }
    }

    showPaymentMethods() {
        if (!this.hasPaymentBlockTarget) {
            return;
        }

        this.paymentBlockTarget.classList.remove('d-none');
        this.paymentBlockTarget.setAttribute('aria-hidden', 'false');
        this.showPaymentMethodsButtonTarget.setAttribute(
            'aria-expanded',
            'true'
        );

        this.paymentBlockTarget.scrollIntoView({
            behavior: 'smooth',
            block: 'nearest'
        });
    }

    hidePaymentMethods() {
        if (!this.hasPaymentBlockTarget) {
            return;
        }

        this.paymentBlockTarget.classList.add('d-none');
        this.paymentBlockTarget.setAttribute('aria-hidden', 'true');

        if (this.hasShowPaymentMethodsButtonTarget) {
            this.showPaymentMethodsButtonTarget.setAttribute(
                'aria-expanded',
                'false'
            );
        }
    }

    selectPaymentMethod(event) {
        this.paymentOptionTargets.forEach((option) => {
            option.classList.remove('achat-forfait-payment-option-selected');
        });

        const selectedOption = event.currentTarget.closest(
            '.achat-forfait-payment-option'
        );

        selectedOption?.classList.add(
            'achat-forfait-payment-option-selected'
        );
    }

    confirmPaymentMethod() {
        const selectedMethod = this.paymentMethodTargets.find(
            (method) => method.checked
        );

        if (!selectedMethod) {
            this.showMessage(
                'Veuillez sélectionner une carte bancaire.',
                true
            );
            return;
        }

        if (this.hasSelectedCardNameTarget) {
            this.selectedCardNameTarget.textContent =
                selectedMethod.dataset.cardName || '';
        }

        if (this.hasSelectedCardExpirationTarget) {
            this.selectedCardExpirationTarget.textContent =
                selectedMethod.dataset.cardExpiration || '';
        }

        this.selectedPaymentMethodId = selectedMethod.value;
        this.hidePaymentMethods();
        this.hideMessage();
    }

    async subscribe() {
        if (!this.hasSubscribeButtonTarget || this.subscribeButtonTarget.disabled) {
            return;
        }

        const planPriceId = this.planPriceIdValue;
        const paymentMethodId = Number(this.selectedPaymentMethodId);

        if (
            !Number.isInteger(planPriceId)
            || planPriceId < 1
            || !Number.isInteger(paymentMethodId)
            || paymentMethodId < 1
        ) {
            this.showMessage(
                'Veuillez ajouter ou sélectionner une carte bancaire.',
                true
            );
            return;
        }

        this.setLoading(true);
        this.hideMessage();

        try {
            const response = await fetch(this.subscriptionUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.subscriptionCsrfValue
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    planPriceId,
                    paymentMethodId
                })
            });
            const data = await this.readJsonResponse(response);

            if (!response.ok || data.success !== true) {
                throw new Error(
                    data.message || 'Le paiement n’a pas pu être effectué.'
                );
            }

            this.showMessage(data.message || 'Votre abonnement est actif.');

            this.redirectTimeout = window.setTimeout(() => {
                window.location.assign(this.redirectUrlValue);
            }, 800);
        } catch (error) {
            console.error('Erreur pendant la souscription Stripe :', error);
            this.showMessage(
                error instanceof Error
                    ? error.message
                    : 'Le paiement n’a pas pu être effectué.',
                true
            );
            this.setLoading(false);
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

    setLoading(loading) {
        this.subscribeButtonTarget.disabled = loading;
        this.subscribeButtonTarget.textContent = loading
            ? 'Traitement…'
            : this.subscribeButtonLabel;
    }

    showMessage(message, error = false) {
        if (!this.hasSubscriptionMessageTarget) {
            return;
        }

        this.subscriptionMessageTarget.textContent = message;
        this.subscriptionMessageTarget.setAttribute(
            'role',
            error ? 'alert' : 'status'
        );
    }

    hideMessage() {
        if (!this.hasSubscriptionMessageTarget) {
            return;
        }

        this.subscriptionMessageTarget.textContent = '';
        this.subscriptionMessageTarget.setAttribute('role', 'status');
    }
}
