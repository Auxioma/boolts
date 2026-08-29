import { Controller } from '@hotwired/stimulus';

/*
 * Achat ponctuel d'un pack boost avec une carte bancaire Stripe déjà enregistrée.
 *
 * Ce contrôleur ne manipule aucune donnée sensible de carte : il choisit une
 * carte existante puis appelle l'endpoint serveur qui crée et confirme le
 * PaymentIntent Stripe. Toute la logique de paiement reste côté serveur.
 *
 * L'ajout d'une nouvelle carte est géré par le contrôleur `payment-method`
 * (modale Stripe Elements), qui recharge la page une fois la carte enregistrée.
 */
export default class extends Controller {
    static targets = [
        'paymentBlock',
        'paymentOption',
        'paymentMethod',
        'showPaymentMethodsButton',
        'selectedCardName',
        'selectedCardExpiration',
        'selectedCardSummary',
        'purchaseButton',
        'purchaseMessage'
    ];

    static values = {
        paymentUrl: String,
        paymentCsrf: String,
        redirectUrl: String,
        defaultPaymentMethodId: String
    };

    connect() {
        this.selectedPaymentMethodId = this.defaultPaymentMethodIdValue || null;
        this.purchaseButtonLabel = this.hasPurchaseButtonTarget
            ? this.purchaseButtonTarget.textContent.trim()
            : 'Acheter le pack boost';
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

        if (this.hasShowPaymentMethodsButtonTarget) {
            this.showPaymentMethodsButtonTarget.setAttribute(
                'aria-expanded',
                'true'
            );
        }

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
        this.applyPaymentMethod(event.currentTarget);
    }

    confirmPaymentMethod() {
        const selectedMethod = this.hasPaymentMethodTarget
            ? this.paymentMethodTargets.find((method) => method.checked)
            : null;

        if (!selectedMethod) {
            return;
        }

        this.applyPaymentMethod(selectedMethod);
    }

    applyPaymentMethod(radio) {
        if (!radio) {
            return;
        }

        this.paymentOptionTargets.forEach((option) => {
            option.classList.remove('achat-forfait-payment-option-selected');
        });

        if (radio.checked) {
            radio
                .closest('.achat-forfait-payment-option')
                ?.classList.add('achat-forfait-payment-option-selected');
        }

        if (this.hasSelectedCardNameTarget) {
            this.selectedCardNameTarget.textContent =
                radio.dataset.cardName || '';
        }

        if (this.hasSelectedCardExpirationTarget) {
            this.selectedCardExpirationTarget.textContent =
                radio.dataset.cardExpiration || '';
        }

        if (this.hasSelectedCardSummaryTarget) {
            this.selectedCardSummaryTarget.textContent = `Carte débitée : ${
                radio.dataset.cardName || ''
            }`;
        }

        this.selectedPaymentMethodId = radio.value;
        this.hidePaymentMethods();
    }

    async purchase() {
        if (!this.hasPurchaseButtonTarget || this.purchaseButtonTarget.disabled) {
            return;
        }

        const paymentMethodId = Number(this.selectedPaymentMethodId);

        if (!Number.isInteger(paymentMethodId) || paymentMethodId < 1) {
            this.showMessage(
                'Veuillez ajouter ou sélectionner une carte bancaire.',
                true
            );
            return;
        }

        this.setLoading(true);

        try {
            const response = await fetch(this.paymentUrlValue, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': this.paymentCsrfValue
                },
                credentials: 'same-origin',
                body: JSON.stringify({ paymentMethodId })
            });
            const data = await this.readJsonResponse(response);

            if (!response.ok || data.success !== true) {
                throw new Error(
                    data.message || 'Le paiement n’a pas pu être effectué.'
                );
            }

            this.showMessage(data.message || 'Votre pack boost a été acheté.');

            this.redirectTimeout = window.setTimeout(() => {
                window.location.assign(this.redirectUrlValue);
            }, 800);
        } catch (error) {
            console.error('Erreur pendant l’achat du pack boost Stripe :', error);
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
        this.purchaseButtonTarget.disabled = loading;
        this.purchaseButtonTarget.textContent = loading
            ? 'Traitement…'
            : this.purchaseButtonLabel;
    }

    showMessage(message, error = false) {
        if (!this.hasPurchaseMessageTarget) {
            return;
        }

        this.purchaseMessageTarget.textContent = message;
        this.purchaseMessageTarget.setAttribute(
            'role',
            error ? 'alert' : 'status'
        );
    }
}
