<?php

namespace App\Entity\Enum;

enum DocumentRequestStatus: string
{
    case WAITING_UPLOAD = 'waiting_upload';
    case UNDER_REVIEW = 'under_review';
    case REJECTED = 'rejected';
    case APPROVED = 'approved';
    case BLOCKED = 'blocked';
}
