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

namespace App\Controller\Traits;

use Symfony\Contracts\Service\Attribute\Required;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Traduction des messages renvoyés par les contrôleurs (JSON, flash, exceptions affichées).
 * Injecté par setter pour ne pas modifier les constructeurs existants.
 */
trait TranslatesMessages
{
    private ?TranslatorInterface $messageTranslator = null;

    #[Required]
    public function setMessageTranslator(TranslatorInterface $messageTranslator): void
    {
        $this->messageTranslator = $messageTranslator;
    }

    /**
     * @param array<string, string|int|float|null> $parameters
     */
    protected function t(string $key, array $parameters = []): string
    {
        return null !== $this->messageTranslator ? $this->messageTranslator->trans($key, $parameters) : $key;
    }
}
