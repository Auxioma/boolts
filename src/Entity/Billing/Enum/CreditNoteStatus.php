<?php

declare(strict_types=1);

namespace App\Entity\Billing\Enum;

enum CreditNoteStatus: string
{
    case DRAFT = 'draft';
    case ISSUED = 'issued';
    case VOID = 'void';
}
