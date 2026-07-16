<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum InvoiceStatus: string
{
    case DRAFT = 'draft';
    case OPEN = 'open';
    case PAID = 'paid';
    case VOID = 'void';
    case UNCOLLECTIBLE = 'uncollectible';
}
