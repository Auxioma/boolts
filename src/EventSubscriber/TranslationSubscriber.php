<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\EventSubscriber;

use App\Service\DatabaseTranslator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Subscriber pour charger les traductions en base de données à chaque requête principale.
 */
class TranslationSubscriber implements EventSubscriberInterface
{
    private DatabaseTranslator $databaseTranslator;

    /**
     * Injection du service DatabaseTranslator.
     */
    public function __construct(DatabaseTranslator $databaseTranslator)
    {
        $this->databaseTranslator = $databaseTranslator;
    }

    /**
     * Définit les événements auxquels ce subscriber réagit.
     * Ici, on réagit à l’événement KernelEvents::REQUEST avec une priorité de 20.
     */
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    /**
     * Méthode appelée lors de chaque requête HTTP.
     * Charge les traductions depuis la base de données uniquement pour la requête principale (pas les sous-requêtes).
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $this->databaseTranslator->loadTranslations();
    }
}
