<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum SubscriptionEmailType: string
{
    case SUBSCRIPTION_PURCHASED = 'subscription_purchased';
    case SUBSCRIPTION_UPGRADED = 'subscription_upgraded';
    case SUBSCRIPTION_DOWNGRADE_SCHEDULED = 'subscription_downgrade_scheduled';
    case PAYMENT_FAILED_FIRST_ATTEMPT = 'payment_failed_first_attempt';
    case PAYMENT_RETRY_FAILED = 'payment_retry_failed';
    case PAYMENT_RECOVERED = 'payment_recovered';
    case PAYMENT_DEFINITIVELY_FAILED = 'payment_definitively_failed';
    case SUBSCRIPTION_CANCEL_REQUESTED = 'subscription_cancel_requested';
    case SUBSCRIPTION_ENDED = 'subscription_ended';
    case DOWNGRADED_TO_FREE = 'downgraded_to_free';

    public function subject(): string
    {
        return match ($this) {
            self::SUBSCRIPTION_PURCHASED => 'Récapitulatif de votre achat d’abonnement',
            self::SUBSCRIPTION_UPGRADED => 'Votre changement de forfait est confirmé',
            self::SUBSCRIPTION_DOWNGRADE_SCHEDULED => 'Votre changement de forfait est programmé',
            self::PAYMENT_FAILED_FIRST_ATTEMPT => 'Échec du renouvellement de votre abonnement',
            self::PAYMENT_RETRY_FAILED => 'Nouvelle tentative de renouvellement échouée',
            self::PAYMENT_RECOVERED => 'Votre abonnement est de nouveau actif',
            self::PAYMENT_DEFINITIVELY_FAILED => 'Votre abonnement n’a pas pu être renouvelé',
            self::SUBSCRIPTION_CANCEL_REQUESTED => 'Votre résiliation est programmée',
            self::SUBSCRIPTION_ENDED => 'Votre abonnement payant est terminé',
            self::DOWNGRADED_TO_FREE => 'Votre compte est repassé sur le forfait gratuit',
        };
    }
}
