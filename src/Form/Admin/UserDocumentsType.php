<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

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
