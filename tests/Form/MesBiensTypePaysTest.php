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

namespace App\Tests\Form;

use App\Entity\Property;
use App\Form\Dashboard\AgenceImmobiliere\MesBiensType;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Form\FormFactoryInterface;

/**
 * Étape 3 du tunnel « Mes biens » : le pays est stocké en base sous forme de
 * libellé ("France"), mais le widget CountryType manipule un code ISO ("FR").
 * Ces tests verrouillent la conversion dans les deux sens.
 */
final class MesBiensTypePaysTest extends KernelTestCase
{
    private FormFactoryInterface $formFactory;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->formFactory = self::getContainer()->get(FormFactoryInterface::class);

        \Locale::setDefault('fr');
    }

    public function testStoredCountryNamePreselectsTheCountryOption(): void
    {
        $property = (new Property())->setPays('France');

        $form = $this->formFactory->create(
            MesBiensType::class,
            $property,
            ['step' => 3, 'typeTransaction' => '1']
        );

        self::assertSame(
            'FR',
            $form->get('pays')->getViewData(),
            'Le pays enregistré ("France") doit être présélectionné via son code ISO.'
        );
    }

    public function testLegacyIsoCodeIsStillPreselected(): void
    {
        $property = (new Property())->setPays('FR');

        $form = $this->formFactory->create(
            MesBiensType::class,
            $property,
            ['step' => 3, 'typeTransaction' => '1']
        );

        self::assertSame('FR', $form->get('pays')->getViewData());
    }

    public function testSubmittedIsoCodeIsPersistedAsALabel(): void
    {
        $property = new Property();

        $form = $this->formFactory->create(
            MesBiensType::class,
            $property,
            ['step' => 3, 'typeTransaction' => '1']
        );

        $form->submit(
            [
                'adresse' => '10 rue de Rivoli',
                'codePostal' => '75001',
                'ville' => 'Paris',
                'pays' => 'FR',
            ],
            false
        );

        self::assertSame(
            'France',
            $property->getPays(),
            'La soumission doit ré-enregistrer un libellé, cohérent avec la recherche publique.'
        );
    }
}
