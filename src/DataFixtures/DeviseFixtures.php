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

namespace App\DataFixtures;

use App\Entity\Devise;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class DeviseFixtures extends Fixture
{
    public const DEVISE_EURO = 'devise_euro';
    public const DEVISE_DOLLAR = 'devise_dollar';

    public function load(ObjectManager $manager): void
    {
        $devises = [
            ['nom' => 'Euro', 'signe' => '€'],
            ['nom' => 'Dollar américain', 'signe' => '$'],
            ['nom' => 'Livre sterling', 'signe' => '£'],
            ['nom' => 'Franc suisse', 'signe' => 'CHF'],
            ['nom' => 'Yen japonais', 'signe' => '¥'],
            ['nom' => 'Yuan chinois', 'signe' => '¥'],
            ['nom' => 'Won sud-coréen', 'signe' => '₩'],
            ['nom' => 'Dollar canadien', 'signe' => 'C$'],
            ['nom' => 'Dollar australien', 'signe' => 'A$'],
            ['nom' => 'Dollar néo-zélandais', 'signe' => 'NZ$'],
            ['nom' => 'Rouble russe', 'signe' => '₽'],
            ['nom' => 'Roupie indienne', 'signe' => '₹'],
            ['nom' => 'Real brésilien', 'signe' => 'R$'],
            ['nom' => 'Peso mexicain', 'signe' => '$'],
            ['nom' => 'Peso argentin', 'signe' => '$'],
            ['nom' => 'Livre turque', 'signe' => '₺'],
            ['nom' => 'Couronne suédoise', 'signe' => 'kr'],
            ['nom' => 'Couronne norvégienne', 'signe' => 'kr'],
            ['nom' => 'Couronne danoise', 'signe' => 'kr'],
            ['nom' => 'Zloty polonais', 'signe' => 'zł'],
            ['nom' => 'Forint hongrois', 'signe' => 'Ft'],
            ['nom' => 'Couronne tchèque', 'signe' => 'Kč'],
            ['nom' => 'Leu roumain', 'signe' => 'lei'],
            ['nom' => 'Lev bulgare', 'signe' => 'лв'],
            ['nom' => 'Hryvnia ukrainienne', 'signe' => '₴'],
            ['nom' => 'Dirham marocain', 'signe' => 'MAD'],
            ['nom' => 'Dinar algérien', 'signe' => 'DZD'],
            ['nom' => 'Dinar tunisien', 'signe' => 'TND'],
            ['nom' => 'Livre égyptienne', 'signe' => 'E£'],
            ['nom' => 'Riyal saoudien', 'signe' => '﷼'],
            ['nom' => 'Dirham des Émirats arabes unis', 'signe' => 'AED'],
            ['nom' => 'Shekel israélien', 'signe' => '₪'],
            ['nom' => 'Baht thaïlandais', 'signe' => '฿'],
            ['nom' => 'Dong vietnamien', 'signe' => '₫'],
            ['nom' => 'Ringgit malaisien', 'signe' => 'RM'],
            ['nom' => 'Dollar de Singapour', 'signe' => 'S$'],
            ['nom' => 'Rupiah indonésienne', 'signe' => 'Rp'],
            ['nom' => 'Peso philippin', 'signe' => '₱'],
            ['nom' => 'Rand sud-africain', 'signe' => 'R'],
            ['nom' => 'Franc CFA BCEAO', 'signe' => 'CFA'],
            ['nom' => 'Franc CFA BEAC', 'signe' => 'FCFA'],
            ['nom' => 'Bitcoin', 'signe' => '₿'],
            ['nom' => 'Ethereum', 'signe' => 'Ξ'],
        ];

        foreach ($devises as $data) {
            $devise = new Devise();
            $devise->setNom($data['nom']);
            $devise->setSigne($data['signe']);

            $manager->persist($devise);

            if ('Euro' === $data['nom']) {
                $this->addReference(self::DEVISE_EURO, $devise);
            }

            if ('Dollar américain' === $data['nom']) {
                $this->addReference(self::DEVISE_DOLLAR, $devise);
            }
        }

        $manager->flush();
    }
}
