<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d'un projet développé par Auxioma Web Agency pour l'entreprise Pastelit Co.
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
     * Pause après chaque image.
     * 250000 = 0.25 seconde.
     */
    private const SLEEP_AFTER_IMAGE_MICROSECONDS = 250000;

    /**
     * Pause après chaque bien.
     */
    private const SLEEP_AFTER_PROPERTY_SECONDS = 1;

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
        if (\function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        if (!\gc_enabled()) {
            \gc_enable();
        }

        $filesystem = new Filesystem();

        $projectDir = $this->kernel->getProjectDir();
        $tempDirectory = $projectDir.self::TEMP_DIRECTORY;

        if ($filesystem->exists($tempDirectory)) {
            $filesystem->remove($tempDirectory);
        }

        $filesystem->mkdir($tempDirectory);

        $properties = $this->propertyRepository->findAll();

        if ([] === $properties) {
            throw new \RuntimeException("Aucun bien trouvé. Lance d'abord les fixtures des biens.");
        }

        foreach ($properties as $property) {
            if (null === $property->getId()) {
                throw new \RuntimeException("Impossible de créer les images : un bien n'a pas encore d'ID.");
            }

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

                /**
                 * Important :
                 * Ne force pas setImageName($imageName) si tu utilises SmartUniqueNamer.
                 * Vich va remplir imageName avec le vrai nom envoyé.
                 */
                if ($filesystem->exists($temporaryImagePath)) {
                    $propertyImage->setImageSize(filesize($temporaryImagePath) ?: null);
                }

                $manager->persist($propertyImage);

                /**
                 * Très important :
                 * On flush après chaque image.
                 * C'est plus lent, mais beaucoup plus sûr sur PlanetHoster.
                 */
                $manager->flush();

                /**
                 * On détache uniquement cette instance de PropertyImage.
                 *
                 * Fix ORM 3.x : clear() n'accepte plus d'argument de classe.
                 * En ORM 3.x, clear(PropertyImage::class) vide silencieusement
                 * tout l'Unit of Work, y compris les entités Property — elles
                 * deviennent "detached" et Doctrine lève une erreur
                 * "A new entity was found through the relationship" à l'itération
                 * suivante, car il ne peut pas les persister sans cascade.
                 * detach($propertyImage) ne cible que cette instance précise ;
                 * les Property restent correctement tracées dans l'UoW.
                 */
                $manager->detach($propertyImage);

                /**
                 * Nettoyage du fichier temporaire si Vich ne l'a pas déjà déplacé/supprimé.
                 */
                if ($filesystem->exists($temporaryImagePath)) {
                    $filesystem->remove($temporaryImagePath);
                }

                /**
                 * Nettoyage mémoire / fichiers ouverts.
                 */
                unset($uploadedFile, $propertyImage);

                \gc_collect_cycles();

                /**
                 * Petite pause pour éviter de saturer PlanetHoster / N0C.
                 */
                usleep(self::SLEEP_AFTER_IMAGE_MICROSECONDS);
            }

            /**
             * Pause plus longue après chaque bien.
             */
            sleep(self::SLEEP_AFTER_PROPERTY_SECONDS);
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

        /**
         * Répartition souhaitée :
         * 80% horizontal
         * 20% vertical
         *
         * Le modulo 5 donne environ 1 image verticale sur 5.
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

        $response = $this->httpClient->request('GET', $url, [
            'timeout' => 30,
            'max_duration' => 60,
        ]);

        if (200 !== $response->getStatusCode()) {
            throw new \RuntimeException(\sprintf("Impossible de télécharger l'image placeholder : %s", $url));
        }

        file_put_contents($imagePath, $response->getContent());

        unset($response);

        return $imagePath;
    }

    public function getDependencies(): array
    {
        return [
            PropertyFixtures::class,
        ];
    }
}