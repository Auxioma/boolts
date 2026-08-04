<?php

namespace App\Form\Admin;

use App\Entity\Document\UserDocumentRequest;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Form\AbstractType;

/**
 * @extends AbstractType<Collection<int, UserDocumentRequest>>
 */
final class UserDocumentsType extends AbstractType
{
    public function getBlockPrefix(): string
    {
        return 'user_documents';
    }
}
