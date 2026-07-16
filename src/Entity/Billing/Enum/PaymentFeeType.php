<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum PaymentFeeType: string
{
    case STRIPE_PROCESSING_FEE = 'stripe_processing_fee';
    case STRIPE_FIXED_FEE = 'stripe_fixed_fee';
    case STRIPE_INTERNATIONAL_CARD_FEE = 'stripe_international_card_fee';
    case STRIPE_CURRENCY_CONVERSION_FEE = 'stripe_currency_conversion_fee';
    case STRIPE_TAX_FEE = 'stripe_tax_fee';
    case REFUND_FEE = 'refund_fee';
    case DISPUTE_FEE = 'dispute_fee';
    case OTHER_PROVIDER_FEE = 'other_provider_fee';
}
