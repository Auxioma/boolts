<?php

declare(strict_types=1);

namespace App\Service\Stripe;

use App\Entity\Billing\AgencyBillingProfile;
use App\Entity\Billing\AgencyPaymentMethod;
use App\Entity\User;
use Stripe\Customer;
use Stripe\StripeClient;

final readonly class StripeCustomerService
{
    public function __construct(
        private StripeClient $stripe,
    ) {
    }

    public function retrieve(string $customerId): Customer
    {
        return $this->stripe->customers->retrieve($customerId);
    }

    public function getOrCreateCustomer(
        User $agency,
        AgencyBillingProfile $billingProfile,
    ): string {
        $stripeCustomerId = $billingProfile->getStripeCustomerId();

        if (\is_string($stripeCustomerId) && str_starts_with($stripeCustomerId, 'cus_')) {
            return $stripeCustomerId;
        }

        $customer = $this->stripe->customers->create([
            'email' => $billingProfile->getBillingEmail() ?: $agency->getEmail(),
            'name' => $billingProfile->getLegalName() ?: $agency->getUserIdentifier(),
            'metadata' => [
                'user_id' => (string) $agency->getId(),
                'billing_profile_id' => (string) $billingProfile->getId(),
            ],
        ]);

        $billingProfile->setStripeCustomerId($customer->id);

        return $customer->id;
    }

    public function updateDefaultPaymentMethod(
        AgencyBillingProfile $billingProfile,
        ?AgencyPaymentMethod $paymentMethod,
    ): void {
        $stripeCustomerId = $billingProfile->getStripeCustomerId();

        if (!\is_string($stripeCustomerId) || !str_starts_with($stripeCustomerId, 'cus_')) {
            return;
        }

        $this->stripe->customers->update(
            $stripeCustomerId,
            [
                'invoice_settings' => [
                    'default_payment_method' => $paymentMethod?->getStripePaymentMethodId(),
                ],
            ],
        );
    }

    public function createBillingPortalSession(
        string $customerId,
        string $returnUrl,
    ): string {
        $session = $this->stripe->billingPortal->sessions->create([
            'customer' => $customerId,
            'return_url' => $returnUrl,
        ]);

        if (!\is_string($session->url) || '' === $session->url) {
            throw new \RuntimeException('Stripe n’a pas retourné d’URL de portail client.');
        }

        return $session->url;
    }
}
