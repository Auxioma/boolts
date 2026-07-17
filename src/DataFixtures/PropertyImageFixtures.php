<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency
 * pour l’entreprise Pastelit Co.
 *
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency
 * et Pastelit Co.
 *
 * Toute reproduction, modification, distribution ou utilisation
 * sans autorisation préalable est interdite.
 */

namespace App\DataFixtures;

use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Repository\PropertyRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class PropertyImageFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * Répertoire temporaire utilisé avant le déplacement des images
     * par VichUploader.
     */
    private const TEMP_DIRECTORY = 'var/fixtures/property-images';

    /**
     * Cache des photographies téléchargées.
     *
     * Cela évite de télécharger plusieurs milliers d’images si beaucoup
     * de biens utilisent les fixtures.
     */
    private const CACHE_DIRECTORY = 'var/fixtures/property-images-cache';

    /**
     * Nombre minimum et maximum d’images attribuées à chaque bien.
     */
    private const PROPERTY_IMAGES_MIN = 3;
    private const PROPERTY_IMAGES_MAX = 6;

    /**
     * Nombre maximum de photographies différentes conservées dans le cache.
     */
    private const CACHE_IMAGES_COUNT = 120;

    /**
     * Dimensions principales des images.
     */
    private const LANDSCAPE_WIDTH = 1200;
    private const LANDSCAPE_HEIGHT = 800;

    private const PORTRAIT_WIDTH = 800;
    private const PORTRAIT_HEIGHT = 1200;

    /**
     * Nombre de tentatives de téléchargement.
     */
    private const DOWNLOAD_ATTEMPTS = 3;

    /**
     * Durée maximale d’un téléchargement.
     */
    private const DOWNLOAD_TIMEOUT = 30;

    /**
     * Taille minimale acceptée pour considérer le fichier comme une image valide.
     */
    private const MINIMUM_IMAGE_SIZE = 10_000;

    /**
     * Mots-clés utilisés pour obtenir des images immobilières.
     */
    private const IMAGE_KEYWORDS = [
        'luxury,apartment,interior',
        'modern,house,interior',
        'living,room,interior',
        'bedroom,interior',
        'kitchen,interior',
        'bathroom,interior',
        'villa,architecture',
        'house,architecture',
        'apartment,building',
        'modern,architecture',
        'terrace,house',
        'garden,villa',
        'real,estate',
        'luxury,home',
        'home,interior',
        'office,interior',
        'commercial,building',
        'loft,interior',
        'studio,apartment',
        'dining,room',
    ];

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly PropertyRepository $propertyRepository,
        private readonly HttpClientInterface $httpClient,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        if (\function_exists('set_time_limit')) {
            @set_time_limit(0);
        }

        if (!gc_enabled()) {
            gc_enable();
        }

        $filesystem = new Filesystem();

        $projectDirectory = $this->kernel->getProjectDir();

        $temporaryDirectory = $projectDirectory
            . \DIRECTORY_SEPARATOR
            . self::TEMP_DIRECTORY;

        $cacheDirectory = $projectDirectory
            . \DIRECTORY_SEPARATOR
            . self::CACHE_DIRECTORY;

        /*
         * Le dossier temporaire est toujours vidé avant le chargement.
         */
        if ($filesystem->exists($temporaryDirectory)) {
            $filesystem->remove($temporaryDirectory);
        }

        $filesystem->mkdir($temporaryDirectory);

        /*
         * Le cache n’est pas supprimé automatiquement.
         *
         * Cela permet de relancer les fixtures sans devoir télécharger
         * à nouveau toutes les photographies.
         */
        if (!$filesystem->exists($cacheDirectory)) {
            $filesystem->mkdir($cacheDirectory);
        }

        $properties = $this->propertyRepository->findAll();

        if ([] === $properties) {
            throw new \RuntimeException(
                "Aucun bien immobilier trouvé. Lance d'abord PropertyFixtures."
            );
        }

        /*
         * On prépare le cache d’images avant de parcourir les biens.
         */
        $this->prepareImageCache(
            filesystem: $filesystem,
            cacheDirectory: $cacheDirectory,
        );

        try {
            foreach ($properties as $property) {
                if (!$property instanceof Property) {
                    continue;
                }

                if (null === $property->getId()) {
                    throw new \RuntimeException(
                        "Impossible de créer les images : un bien n'a pas d'identifiant."
                    );
                }

                $propertyId = (int) $property->getId();

                /*
                 * On supprime les anciennes images liées au bien.
                 *
                 * Cela évite les doublons lorsque les fixtures sont relancées.
                 */
                $this->removeExistingImages(
                    manager: $manager,
                    property: $property,
                );

                $manager->flush();

                $temporaryImagePaths = [];
                $createdPropertyImages = [];

                try {
                    $imagesCount = $this->getImagesCount($propertyId);

                    for ($position = 1; $position <= $imagesCount; ++$position) {
                        $imageName = $this->getImageName(
                            propertyId: $propertyId,
                            position: $position,
                        );

                        $temporaryImagePath = $this->createTemporaryImage(
                            filesystem: $filesystem,
                            cacheDirectory: $cacheDirectory,
                            temporaryDirectory: $temporaryDirectory,
                            propertyId: $propertyId,
                            position: $position,
                            imageName: $imageName,
                        );

                        $temporaryImagePaths[] = $temporaryImagePath;

                        $mimeType = mime_content_type($temporaryImagePath);

                        if (
                            false === $mimeType
                            || !str_starts_with($mimeType, 'image/')
                        ) {
                            throw new \RuntimeException(
                                \sprintf(
                                    'Le fichier téléchargé n’est pas une image valide : %s',
                                    $temporaryImagePath
                                )
                            );
                        }

                        $uploadedFile = new UploadedFile(
                            path: $temporaryImagePath,
                            originalName: $imageName,
                            mimeType: $mimeType,
                            error: null,
                            test: true,
                        );

                        $propertyImage = new PropertyImage();

                        $propertyImage->setProperty($property);
                        $propertyImage->setPosition((string) $position);
                        $propertyImage->setImageFile($uploadedFile);

                        if ($filesystem->exists($temporaryImagePath)) {
                            $fileSize = filesize($temporaryImagePath);

                            $propertyImage->setImageSize(
                                false !== $fileSize ? $fileSize : null
                            );
                        }

                        $manager->persist($propertyImage);

                        $createdPropertyImages[] = $propertyImage;
                    }

                    /*
                     * Le flush déclenche VichUploader.
                     *
                     * Les fichiers temporaires sont alors déplacés vers
                     * le dossier d’upload défini dans la configuration.
                     */
                    $manager->flush();

                    foreach ($createdPropertyImages as $propertyImage) {
                        $manager->detach($propertyImage);
                    }
                } finally {
                    /*
                     * Nettoyage des fichiers temporaires qui n’auraient
                     * pas été déplacés.
                     */
                    foreach ($temporaryImagePaths as $temporaryImagePath) {
                        if ($filesystem->exists($temporaryImagePath)) {
                            $filesystem->remove($temporaryImagePath);
                        }
                    }

                    unset(
                        $temporaryImagePaths,
                        $createdPropertyImages,
                    );

                    gc_collect_cycles();
                }

                $manager->detach($property);
            }
        } finally {
            if ($filesystem->exists($temporaryDirectory)) {
                $filesystem->remove($temporaryDirectory);
            }
        }
    }

    /**
     * Télécharge les photographies manquantes dans le cache.
     */
    private function prepareImageCache(
        Filesystem $filesystem,
        string $cacheDirectory,
    ): void {
        for ($index = 1; $index <= self::CACHE_IMAGES_COUNT; ++$index) {
            $isPortrait = 0 === $index % 5;

            $width = $isPortrait
                ? self::PORTRAIT_WIDTH
                : self::LANDSCAPE_WIDTH;

            $height = $isPortrait
                ? self::PORTRAIT_HEIGHT
                : self::LANDSCAPE_HEIGHT;

            $cacheFileName = $this->getCacheImageName(
                index: $index,
                width: $width,
                height: $height,
            );

            $cacheFilePath = $cacheDirectory
                . \DIRECTORY_SEPARATOR
                . $cacheFileName;

            /*
             * On conserve les images déjà présentes et valides.
             */
            if (
                $filesystem->exists($cacheFilePath)
                && $this->isValidImage($cacheFilePath)
            ) {
                continue;
            }

            /*
             * Si le fichier existe mais est invalide, on le supprime.
             */
            if ($filesystem->exists($cacheFilePath)) {
                $filesystem->remove($cacheFilePath);
            }

            $keyword = self::IMAGE_KEYWORDS[
                ($index - 1) % \count(self::IMAGE_KEYWORDS)
            ];

            $this->downloadImage(
                destinationPath: $cacheFilePath,
                width: $width,
                height: $height,
                keyword: $keyword,
                seed: $index,
            );
        }
    }

    /**
     * Crée une copie temporaire d’une photographie présente dans le cache.
     */
    private function createTemporaryImage(
        Filesystem $filesystem,
        string $cacheDirectory,
        string $temporaryDirectory,
        int $propertyId,
        int $position,
        string $imageName,
    ): string {
        $cacheIndex = $this->getCacheIndex(
            propertyId: $propertyId,
            position: $position,
        );

        $isPortrait = 0 === $cacheIndex % 5;

        $width = $isPortrait
            ? self::PORTRAIT_WIDTH
            : self::LANDSCAPE_WIDTH;

        $height = $isPortrait
            ? self::PORTRAIT_HEIGHT
            : self::LANDSCAPE_HEIGHT;

        $cacheFileName = $this->getCacheImageName(
            index: $cacheIndex,
            width: $width,
            height: $height,
        );

        $cacheFilePath = $cacheDirectory
            . \DIRECTORY_SEPARATOR
            . $cacheFileName;

        if (!$filesystem->exists($cacheFilePath)) {
            throw new \RuntimeException(
                \sprintf(
                    'L’image cache est introuvable : %s',
                    $cacheFilePath
                )
            );
        }

        if (!$this->isValidImage($cacheFilePath)) {
            throw new \RuntimeException(
                \sprintf(
                    'L’image cache est invalide : %s',
                    $cacheFilePath
                )
            );
        }

        $temporaryFilePath = $temporaryDirectory
            . \DIRECTORY_SEPARATOR
            . $imageName;

        $filesystem->copy(
            originFile: $cacheFilePath,
            targetFile: $temporaryFilePath,
            overwriteNewerFiles: true,
        );

        return $temporaryFilePath;
    }

    /**
     * Télécharge une image depuis LoremFlickr.
     *
     * En cas d’échec, Lorem Picsum est utilisé comme secours.
     */
    private function downloadImage(
        string $destinationPath,
        int $width,
        int $height,
        string $keyword,
        int $seed,
    ): void {
        $loremFlickrUrl = \sprintf(
            'https://loremflickr.com/%d/%d/%s?lock=%d',
            $width,
            $height,
            $keyword,
            $seed,
        );

        $picsumUrl = \sprintf(
            'https://picsum.photos/seed/boolts-property-%d/%d/%d',
            $seed,
            $width,
            $height,
        );

        $sources = [
            $loremFlickrUrl,
            $picsumUrl,
        ];

        $lastException = null;

        foreach ($sources as $sourceUrl) {
            for ($attempt = 1; $attempt <= self::DOWNLOAD_ATTEMPTS; ++$attempt) {
                try {
                    $response = $this->httpClient->request(
                        method: 'GET',
                        url: $sourceUrl,
                        options: [
                            'timeout' => self::DOWNLOAD_TIMEOUT,
                            'max_redirects' => 10,
                            'headers' => [
                                'Accept' => 'image/avif,image/webp,image/jpeg,image/*,*/*;q=0.8',
                                'User-Agent' => 'Boolts-Property-Fixtures/1.0',
                            ],
                        ],
                    );

                    $statusCode = $response->getStatusCode();

                    if ($statusCode < 200 || $statusCode >= 300) {
                        throw new \RuntimeException(
                            \sprintf(
                                'Erreur HTTP %d pour %s',
                                $statusCode,
                                $sourceUrl
                            )
                        );
                    }

                    /*
                     * getContent() suit les redirections grâce à max_redirects.
                     */
                    $content = $response->getContent();

                    if (\strlen($content) < self::MINIMUM_IMAGE_SIZE) {
                        throw new \RuntimeException(
                            \sprintf(
                                'Image trop petite téléchargée depuis %s.',
                                $sourceUrl
                            )
                        );
                    }

                    if (false === file_put_contents($destinationPath, $content)) {
                        throw new \RuntimeException(
                            \sprintf(
                                'Impossible d’écrire l’image dans %s.',
                                $destinationPath
                            )
                        );
                    }

                    if (!$this->isValidImage($destinationPath)) {
                        @unlink($destinationPath);

                        throw new \RuntimeException(
                            \sprintf(
                                'Le contenu téléchargé depuis %s n’est pas une image valide.',
                                $sourceUrl
                            )
                        );
                    }

                    return;
                } catch (
                    TransportExceptionInterface
                    | \RuntimeException
                    | \Throwable $exception
                ) {
                    $lastException = $exception;

                    if (is_file($destinationPath)) {
                        @unlink($destinationPath);
                    }

                    if ($attempt < self::DOWNLOAD_ATTEMPTS) {
                        usleep(300_000);
                    }
                }
            }
        }

        throw new \RuntimeException(
            \sprintf(
                'Impossible de télécharger une image après plusieurs tentatives. Dernière erreur : %s',
                $lastException?->getMessage() ?? 'erreur inconnue'
            ),
            previous: $lastException,
        );
    }

    /**
     * Vérifie que le fichier est une véritable image exploitable.
     */
    private function isValidImage(string $filePath): bool
    {
        if (!is_file($filePath)) {
            return false;
        }

        $fileSize = filesize($filePath);

        if (
            false === $fileSize
            || $fileSize < self::MINIMUM_IMAGE_SIZE
        ) {
            return false;
        }

        $imageInformation = @getimagesize($filePath);

        if (false === $imageInformation) {
            return false;
        }

        $mimeType = $imageInformation['mime'] ?? null;

        return \is_string($mimeType)
            && str_starts_with($mimeType, 'image/');
    }

    /**
     * Supprime les anciennes images en base.
     */
    private function removeExistingImages(
        ObjectManager $manager,
        Property $property,
    ): void {
        $existingImages = $manager
            ->getRepository(PropertyImage::class)
            ->findBy([
                'property' => $property,
            ]);

        foreach ($existingImages as $existingImage) {
            $manager->remove($existingImage);
        }
    }

    /**
     * Retourne entre 3 et 6 images selon l’identifiant du bien.
     */
    private function getImagesCount(int $propertyId): int
    {
        $range = self::PROPERTY_IMAGES_MAX
            - self::PROPERTY_IMAGES_MIN
            + 1;

        return self::PROPERTY_IMAGES_MIN
            + ($propertyId % $range);
    }

    /**
     * Nom final transmis à VichUploader.
     */
    private function getImageName(
        int $propertyId,
        int $position,
    ): string {
        return \sprintf(
            'property-%06d-image-%02d.jpg',
            $propertyId,
            $position,
        );
    }

    /**
     * Calcule l’image du cache affectée à un bien.
     */
    private function getCacheIndex(
        int $propertyId,
        int $position,
    ): int {
        return (
            (($propertyId * 7) + ($position * 13))
            % self::CACHE_IMAGES_COUNT
        ) + 1;
    }

    /**
     * Nom d’une photographie stockée dans le cache.
     */
    private function getCacheImageName(
        int $index,
        int $width,
        int $height,
    ): string {
        return \sprintf(
            'real-estate-%03d-%dx%d.jpg',
            $index,
            $width,
            $height,
        );
    }

    public function getDependencies(): array
    {
        return [
            PropertyFixtures::class,
        ];
    }
}