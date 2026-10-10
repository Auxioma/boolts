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

namespace App\DataFixtures;

use App\Entity\Caracteristique;
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Vich\UploaderBundle\FileAbstraction\ReplacingFile;

/**
 * Biens réels (10 ventes + 10 locations par agence) relevés en octobre 2026 sur
 * les sites de BARNES (Paris), Kensington Morocco et Corcoran (New York).
 *
 * Les données et les photos sont stockées dans Data/Property : properties.json
 * et images/<agence>/<référence>/NN.jpg.
 */
final class PropertyFixtures extends Fixture implements DependentFixtureInterface
{
    private const DATA_DIRECTORY = __DIR__.'/Data/Property';

    private const TRANSLATION_FIELDS = [
        'titreDuLogement',
        'descriptionLogement',
        'adresse',
        'ville',
        'pays',
        'region',
        'district',
        'neighborhood',
        'fullAddress',
    ];

    private const MIN_IMAGES = 3;
    private const MAX_IMAGES = 7;

    public function load(ObjectManager $manager): void
    {
        $properties = $this->readProperties();
        $imagesByAgency = [];

        foreach ($properties as $data) {
            $imagesByAgency[$data['agency']] = [...$imagesByAgency[$data['agency']] ?? [], ...$data['images']];
        }

        foreach ($properties as $data) {
            $property = FixtureEntityHelper::findOrCreate($manager, Property::class, [
                'referenceInterne' => $data['referenceInterne'],
            ]);

            $property
                ->setUser($this->getReference(UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$data['agency'], User::class))
                ->setReferenceInterne($data['referenceInterne'])
                ->setCreatedAt(self::randomDateInLastSixMonths())
                ->setSlug($data['slug'])
                ->setStatut(StatutAnnonceImmobiliere::PUBLIEE)
                ->setTypeBien($this->getReference(
                    CategoryBienFixtures::CATEGORY_BIEN_REFERENCE_PREFIX.$data['typeBien'],
                    CategoryBien::class,
                ))
                ->setTypeTransaction($this->getReference(
                    CategoryBienTransactionFixtures::CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX.$data['transaction'],
                    CategoryBienTransaction::class,
                ))
                ->setCodeIsoPays($data['codeIsoPays'])
                ->setCodePostal($data['codePostal'])
                ->setLatitude($data['latitude'])
                ->setLongitude($data['longitude'])
                ->setShowAdresse(true)
                ->setAnneeConstruction($data['anneeConstruction'])
                ->setChambres($data['chambres'])
                ->setSalleDeBains($data['salleDeBains'])
                ->setSurfaceTotal($data['surfaceTotal'])
                ->setDpe($data['dpe'])
                ->setDpeLettre($data['dpeLettre'])
                ->setGes($data['ges'])
                ->setGesLettre($data['gesLettre'])
                ->setPrix($data['prix'])
                ->setMontantLoyerHorsCharge($data['montantLoyerHorsCharge'])
                ->setMontantDesCharges($data['montantDesCharges'])
                ->setMontantDepotDeGarantie($data['montantDepotDeGarantie']);

            foreach ($property->getCaracteristique() as $caracteristique) {
                $property->removeCaracteristique($caracteristique);
            }

            foreach ($data['caracteristiques'] as $reference) {
                $property->addCaracteristique($this->getReference(
                    CaracteristiqueFixtures::CARACTERISTIQUE_REFERENCE_PREFIX.$reference,
                    Caracteristique::class,
                ));
            }

            foreach ($data['translations'] as $locale => $values) {
                $translation = $property->translate($locale, false);

                foreach (self::TRANSLATION_FIELDS as $field) {
                    $translation->{'set'.ucfirst($field)}($values[$field]);
                }
            }

            $property->mergeNewTranslations();
            $manager->persist($property);

            /*
             * L'identifiant du bien est nécessaire au PropertyDirectoryNamer
             * pour ranger les photos dans public/properties/<id>/.
             */
            $manager->flush();

            if ($property->getPropertyImages()->isEmpty()) {
                $this->attachImages($manager, $property, $this->pickImages($data['images'], $imagesByAgency[$data['agency']]));
            }
        }

        $manager->flush();
    }

    /**
     * Tire entre MIN_IMAGES et MAX_IMAGES photos : celles du bien d'abord
     * (la première reste la couverture), puis, si le bien n'en a pas assez,
     * des photos d'autres biens de la même agence.
     *
     * @param list<string> $own
     * @param list<string> $agencyPool
     *
     * @return list<string>
     */
    private function pickImages(array $own, array $agencyPool): array
    {
        $count = random_int(self::MIN_IMAGES, self::MAX_IMAGES);

        if ($count <= \count($own)) {
            return \array_slice($own, 0, $count);
        }

        $extra = array_values(array_diff($agencyPool, $own));
        shuffle($extra);

        return [...$own, ...\array_slice($extra, 0, $count - \count($own))];
    }

    /**
     * @param list<string> $images
     */
    private function attachImages(ObjectManager $manager, Property $property, array $images): void
    {
        foreach ($images as $index => $image) {
            $path = self::DATA_DIRECTORY.'/images/'.$image;

            if (!is_file($path)) {
                throw new \RuntimeException(\sprintf('Image de fixture introuvable : %s', $path));
            }

            /*
             * VichUploader copie un ReplacingFile (au lieu de le déplacer),
             * le fichier source de la fixture reste donc intact.
             */
            $propertyImage = (new PropertyImage())
                ->setImageFile(new ReplacingFile($path))
                ->setPosition($index + 1);

            $property->addPropertyImage($propertyImage);
            $manager->persist($propertyImage);
        }
    }

    /**
     * Date aléatoire entre il y a 6 mois et maintenant.
     */
    private static function randomDateInLastSixMonths(): \DateTimeImmutable
    {
        $now = new \DateTimeImmutable();
        $from = $now->modify('-6 months');

        return $now->setTimestamp(random_int($from->getTimestamp(), $now->getTimestamp()));
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function readProperties(): array
    {
        $json = file_get_contents(self::DATA_DIRECTORY.'/properties.json');

        if (false === $json) {
            throw new \RuntimeException('Impossible de lire Data/Property/properties.json.');
        }

        return json_decode($json, true, flags: \JSON_THROW_ON_ERROR);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            CategoryBienFixtures::class,
            CategoryBienTransactionFixtures::class,
            CaracteristiqueFixtures::class,
        ];
    }
}
