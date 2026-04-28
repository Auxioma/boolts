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

namespace App\Service;

use App\Repository\TranslationRepository;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\MessageCatalogueInterface;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Contracts\Translation\LocaleAwareInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Service de traduction personnalisé utilisant une base de données.
 */
class DatabaseTranslator implements TranslatorInterface, TranslatorBagInterface, LocaleAwareInterface
{
    // Symfony translator par défaut (fichiers)
    private $translator;

    // Repository pour accéder aux traductions en base de données
    private $translationRepository;

    // Tableau des traductions chargées depuis la base
    private $translations = [];

    // Langue actuelle
    private $locale;

    /**
     * Constructeur.
     *
     * @param TranslatorInterface   $translator            Symfony translator de fallback
     * @param TranslationRepository $translationRepository Repository pour les traductions en DB
     */
    public function __construct(TranslatorInterface $translator, TranslationRepository $translationRepository)
    {

        $this->translator = $translator;
        $this->translationRepository = $translationRepository;
        $this->locale = $translator->getLocale(); // Initialise avec la locale par défaut
    }

    /**
     * Charge toutes les traductions depuis la base de données.
     * Les stocke dans le tableau $translations par locale et clé.
     */
    public function loadTranslations()
    {
        $translations = $this->translationRepository->findAll();

        foreach ($translations as $translation) {
            $this->translations[$translation->getLocale()][$translation->getTranslationKey()] = $translation->getTranslation();
        }
    }

    /**
     * Traduit un identifiant.
     *
     * @param string      $id         Identifiant de traduction
     * @param array       $parameters Paramètres à injecter dans la traduction
     * @param string|null $domain     Non utilisé ici mais requis par l'interface
     * @param string|null $locale     Locale à utiliser
     *
     * @return string La chaîne traduite
     */
    public function trans(string $id, array $parameters = [], ?string $domain = null, ?string $locale = null): string
    {
        $locale = $locale ?? $this->getLocale();

        // Si la traduction existe dans la base, on l'utilise
        if (isset($this->translations[$locale][$id])) {
            return strtr($this->translations[$locale][$id], $parameters);
        }

        // Sinon fallback sur le traducteur Symfony par défaut
        return $this->translator->trans($id, $parameters, $domain, $locale);
    }

    /**
     * Retourne la locale actuelle.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * Définit la locale actuelle.
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * Retourne un catalogue de traductions pour une locale donnée.
     *
     * @param string|null $locale Locale cible (ou celle courante si null)
     *
     * @return MessageCatalogueInterface Catalogue Symfony des traductions
     */
    public function getCatalogue(?string $locale = null): MessageCatalogueInterface
    {
        $locale = $locale ?? $this->getLocale();
        $catalogue = new MessageCatalogue($locale);

        if (isset($this->translations[$locale])) {
            foreach ($this->translations[$locale] as $key => $translation) {
                $catalogue->set($key, $translation);
            }
        }

        return $catalogue;
    }

    /**
     * Retourne tous les catalogues de traduction disponibles (toutes les locales chargées).
     *
     * @return MessageCatalogueInterface[] Liste des catalogues
     */
    public function getCatalogues(): array
    {
        $catalogues = [];

        foreach (array_keys($this->translations) as $locale) {
            $catalogues[] = $this->getCatalogue($locale);
        }

        return $catalogues;
    }
}
