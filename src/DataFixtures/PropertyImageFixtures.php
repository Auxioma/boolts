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
    private const UPLOAD_DIRECTORY = '/public/uploads/biens';

    private const PROPERTY_IMAGES_MIN = 3;
    private const PROPERTY_IMAGES_MAX = 6;

    private const IMAGE_WIDTH = 1200;
    private const IMAGE_HEIGHT = 800;

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

        $tempDirectory = $projectDir . self::TEMP_DIRECTORY;
        $uploadDirectory = $projectDir . self::UPLOAD_DIRECTORY;

        if ($filesystem->exists($tempDirectory)) {
            $filesystem->remove($tempDirectory);
        }

        $filesystem->mkdir($tempDirectory);
        $filesystem->mkdir($uploadDirectory);

        $properties = $this->propertyRepository->findAll();

        if ([] === $properties) {
            throw new \RuntimeException('Aucun bien trouvé. Lance d’abord les fixtures des biens.');
        }

        foreach ($properties as $property) {
            if (null === $property->getId()) {
                throw new \RuntimeException('Impossible de créer les images : un bien n’a pas encore d’ID.');
            }

            $imagesCount = $this->getImagesCount($property->getId());

            for ($position = 1; $position <= $imagesCount; $position++) {
                $imageName = $this->getImageName(
                    propertyId: $property->getId(),
                    position: $position
                );

                $serverImagePath = $uploadDirectory . '/' . $imageName;

                $propertyImage = new PropertyImage();
                $propertyImage->setProperty($property);
                $propertyImage->setPosition((string) $position);

                if ($filesystem->exists($serverImagePath)) {
                    /**
                     * L’image existe déjà sur le serveur.
                     * On ne télécharge rien.
                     * On ne demande pas à Vich de déplacer le fichier.
                     * On insère seulement les informations en BDD.
                     */
                    $propertyImage->setImageName($imageName);
                    $propertyImage->setImageSize(filesize($serverImagePath) ?: null);
                } else {
                    /**
                     * L’image n’existe pas.
                     * On la télécharge dans var/fixtures/property-images.
                     */
                    $temporaryImagePath = $this->downloadPlaceholderImage(
                        tempDirectory: $tempDirectory,
                        propertyId: $property->getId(),
                        position: $position,
                        imageName: $imageName
                    );

                    /**
                     * On prépare un vrai UploadedFile pour VichUploader.
                     * Le dernier argument "true" indique que c’est un fichier de test,
                     * donc Symfony accepte un fichier déjà présent sur le disque.
                     */
                    $uploadedFile = new UploadedFile(
                        path: $temporaryImagePath,
                        originalName: $imageName,
                        mimeType: 'image/jpeg',
                        error: null,
                        test: true
                    );

                    /**
                     * VichUploader déplacera le fichier dans public/uploads/biens.
                     */
                    $propertyImage->setImageFile($uploadedFile);

                    /**
                     * Sécurité :
                     * On renseigne aussi la BDD manuellement avec le même nom stable.
                     * Comme le namer Vich utilise exactement ce nom, le fichier réel
                     * et la valeur imageName resteront identiques.
                     */
                    $propertyImage->setImageName($imageName);
                    $propertyImage->setImageSize(filesize($temporaryImagePath) ?: null);
                }

                $manager->persist($propertyImage);
            }
        }

        $manager->flush();

        $filesystem->remove($tempDirectory);
    }

    private function getImagesCount(int $propertyId): int
    {
        $range = self::PROPERTY_IMAGES_MAX - self::PROPERTY_IMAGES_MIN + 1;

        return self::PROPERTY_IMAGES_MIN + ($propertyId % $range);
    }

    private function getImageName(int $propertyId, int $position): string
    {
        return sprintf(
            'property-%03d-image-%02d.jpg',
            $propertyId,
            $position
        );
    }

    private function downloadPlaceholderImage(
        string $tempDirectory,
        int $propertyId,
        int $position,
        string $imageName
    ): string {
        $seed = sprintf('boolts-property-%d-image-%d', $propertyId, $position);

        $url = sprintf(
            'https://picsum.photos/seed/%s/%d/%d',
            $seed,
            self::IMAGE_WIDTH,
            self::IMAGE_HEIGHT
        );

        $imagePath = $tempDirectory . '/' . $imageName;

        $response = $this->httpClient->request('GET', $url);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(sprintf(
                'Impossible de télécharger l’image placeholder : %s',
                $url
            ));
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