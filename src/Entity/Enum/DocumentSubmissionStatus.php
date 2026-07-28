<?php

namespace App\Entity\Enum;

enum DocumentSubmissionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
