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

namespace App\Service\Property;

use App\Entity\Property;

/**
 * Construit les libellés des notifications agence liées au cycle de vie
 * d'une annonce (soumission, acceptation, refus).
 *
 * Règle commune d'identification de l'annonce :
 * - si l'annonce a un titre : « {titre} » ;
 * - sinon : {type de transaction} {type de bien} {ville}.
 */
final class PropertyNotificationLabeler
{
    /**
     * La colonne agency_notification.nom est limitée à 255 caractères.
     */
    private const MAX_LENGTH = 255;

    public function pendingLabel(Property $property): string
    {
        return $this->compose($property, 'est en attente d’acceptation');
    }

    public function acceptedLabel(Property $property): string
    {
        return $this->compose($property, 'a été acceptée');
    }

    public function refusedLabel(Property $property): string
    {
        return $this->compose($property, 'est refusée', 'Modifiez-la.');
    }

    /**
     * Libellé affiché lorsqu'un Boost vient d'être activé sur une annonce.
     */
    public function boostActiveLabel(Property $property): string
    {
        $name = $this->identify($property);

        $label = '' === $name
            ? 'Le Boost de votre annonce est désormais actif.'
            : \sprintf('Le Boost de « %s » est désormais actif.', $name);

        return mb_strlen($label) > self::MAX_LENGTH
            ? mb_substr($label, 0, self::MAX_LENGTH)
            : $label;
    }

    private function compose(Property $property, string $outcome, string $suffix = ''): string
    {
        $descriptor = $this->describe($property);

        $label = '' === $descriptor
            ? \sprintf('L’annonce %s.', $outcome)
            : \sprintf('L’annonce %s %s.', $descriptor, $outcome);

        if ('' !== $suffix) {
            $label .= ' '.$suffix;
        }

        return mb_strlen($label) > self::MAX_LENGTH
            ? mb_substr($label, 0, self::MAX_LENGTH)
            : $label;
    }

    /**
     * Partie « identifiante » de l'annonce : titre entre guillemets, ou à
     * défaut la concaténation transaction / type de bien / ville. Chaîne
     * vide si aucune de ces informations n'est disponible.
     */
    private function describe(Property $property): string
    {
        $titre = mb_trim((string) $property->getTitreDuLogement());

        if ('' !== $titre) {
            return \sprintf('« %s »', $titre);
        }

        return $this->identify($property);
    }

    /**
     * Nom « nu » de l'annonce (sans guillemets) : titre si disponible, sinon
     * concaténation transaction / type de bien / ville. Chaîne vide si aucune
     * information n'est exploitable.
     */
    private function identify(Property $property): string
    {
        $titre = mb_trim((string) $property->getTitreDuLogement());

        if ('' !== $titre) {
            return $titre;
        }

        $parts = array_filter(
            [
                $property->getTypeTransaction()?->getName(),
                $property->getTypeBien()?->getName(),
                $property->getVille(),
            ],
            static fn (?string $value): bool => null !== $value && '' !== mb_trim($value)
        );

        return mb_trim(implode(' ', $parts));
    }
}
