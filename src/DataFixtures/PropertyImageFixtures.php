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

use App\Entity\PropertyImage;
use App\Repository\PropertyRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class PropertyImageFixtures extends Fixture implements DependentFixtureInterface
{
    private const TEMP_DIRECTORY = '/var/fixtures/property-images';

    private const PROPERTY_IMAGES_MIN = 3;
    private const PROPERTY_IMAGES_MAX = 6;

    /**
     * Photo iPhone verticale.
     */
    private const IMAGE_PORTRAIT_WIDTH = 3024;
    private const IMAGE_PORTRAIT_HEIGHT = 4032;

    /**
     * Photo iPhone horizontale.
     */
    private const IMAGE_LANDSCAPE_WIDTH = 4032;
    private const IMAGE_LANDSCAPE_HEIGHT = 3024;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly HttpClientInterface $httpClient,
        private readonly PropertyRepository $propertyRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $filesystem = new Filesystem();

        $projectDir = $this->kernel->getProjectDir();
        $tempDirectory = $projectDir.self::TEMP_DIRECTORY;

        if ($filesystem->exists($tempDirectory)) {
            $filesystem->remove($tempDirectory);
        }

        $filesystem->mkdir($tempDirectory);

        $properties = $this->propertyRepository->findAll();

        if ([] === $properties) {
            throw new \RuntimeException('Aucun bien trouvé. Lance d’abord les fixtures des biens.');
        }

        foreach ($properties as $property) {
            if (null === $property->getId()) {
                throw new \RuntimeException('Impossible de créer les images : un bien n’a pas encore d’ID.');
            }

            $temporaryFiles = [];
            $imagesCount = $this->getImagesCount($property->getId());

            for ($position = 1; $position <= $imagesCount; ++$position) {
                $imageName = $this->getImageName(
                    propertyId: $property->getId(),
                    position: $position
                );

                $temporaryImagePath = $this->downloadPlaceholderImage(
                    tempDirectory: $tempDirectory,
                    propertyId: $property->getId(),
                    position: $position,
                    imageName: $imageName
                );

                $temporaryFiles[] = $temporaryImagePath;

                /*
                 * Le dernier argument "true" indique que c’est un fichier de test.
                 * Symfony accepte donc un fichier déjà présent sur le disque.
                 */
                $uploadedFile = new UploadedFile(
                    path: $temporaryImagePath,
                    originalName: $imageName,
                    mimeType: 'image/jpeg',
                    error: null,
                    test: true
                );

                $propertyImage = new PropertyImage();
                $propertyImage->setProperty($property);
                $propertyImage->setPosition((string) $position);
                $propertyImage->setImageFile($uploadedFile);

                /*
                 * Important :
                 * Avec SmartUniqueNamer, Vich va générer le vrai nom final du fichier.
                 * Donc on ne force pas setImageName($imageName) ici.
                 *
                 * Si tu forces setImageName(), tu risques d’avoir un nom en BDD
                 * différent du nom réellement envoyé sur N0C S3.
                 */
                $manager->persist($propertyImage);
            }

            /*
             * Très important sur PlanetHoster :
             * On flush par bien pour éviter d’avoir trop de fichiers ouverts en même temps.
             */
            $manager->flush();

            /*
             * On détache uniquement les images pour libérer la mémoire.
             * On ne clear pas les Property, sinon les biens peuvent devenir détachés.
             */
            $manager->clear(PropertyImage::class);

            foreach ($temporaryFiles as $temporaryFile) {
                if ($filesystem->exists($temporaryFile)) {
                    $filesystem->remove($temporaryFile);
                }
            }
        }

        if ($filesystem->exists($tempDirectory)) {
            $filesystem->remove($tempDirectory);
        }
    }

    private function getImagesCount(int $propertyId): int
    {
        $range = self::PROPERTY_IMAGES_MAX - self::PROPERTY_IMAGES_MIN + 1;

        return self::PROPERTY_IMAGES_MIN + ($propertyId % $range);
    }

    private function getImageName(int $propertyId, int $position): string
    {
        return \sprintf(
            'property-%03d-image-%02d.jpg',
            $propertyId,
            $position
        );
    }

    private function downloadPlaceholderImage(
        string $tempDirectory,
        int $propertyId,
        int $position,
        string $imageName,
    ): string {
        $seed = \sprintf('boolts-property-%d-image-%d', $propertyId, $position);

        /*
         * Répartition souhaitée :
         * 80% horizontal
         * 20% vertical
         *
         * Le modulo 5 donne 1 image verticale sur 5.
         */
        $isPortrait = (($propertyId + $position) % 5) === 0;

        $width = $isPortrait
            ? self::IMAGE_PORTRAIT_WIDTH
            : self::IMAGE_LANDSCAPE_WIDTH;

        $height = $isPortrait
            ? self::IMAGE_PORTRAIT_HEIGHT
            : self::IMAGE_LANDSCAPE_HEIGHT;

        $url = \sprintf(
            'https://picsum.photos/seed/%s/%d/%d',
            rawurlencode($seed),
            $width,
            $height
        );

        $imagePath = $tempDirectory.'/'.$imageName;

        $response = $this->httpClient->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(\sprintf('Impossible de télécharger l’image placeholder : %s', $url));
        }

        file_put_contents($imagePath, $response->getContent());

        return $imagePath;
    }

    public function getDependencies(): array
    {
        return [
            PropertyFixtures::class,
        ];
    }
}