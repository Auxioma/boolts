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

class PropertyImageFixtures extends Fixture implements DependentFixtureInterface
{
    private const TEMP_DIRECTORY = '/var/fixtures/property-images';

    private const PROPERTY_IMAGES_MIN = 3;
    private const PROPERTY_IMAGES_MAX = 6;

    /**
     * Photo horizontale.
     */
    private const IMAGE_LANDSCAPE_WIDTH = 1200;
    private const IMAGE_LANDSCAPE_HEIGHT = 800;

    /**
     * Photo verticale.
     */
    private const IMAGE_PORTRAIT_WIDTH = 800;
    private const IMAGE_PORTRAIT_HEIGHT = 1200;

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly PropertyRepository $propertyRepository,
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

        if (!\extension_loaded('gd')) {
            throw new \RuntimeException("L'extension PHP GD est obligatoire pour générer les images locales.");
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

        try {
            foreach ($properties as $property) {
                if (null === $property->getId()) {
                    throw new \RuntimeException("Impossible de créer les images : un bien n'a pas encore d'ID.");
                }

                $propertyImages = [];
                $temporaryImagePaths = [];

                $imagesCount = $this->getImagesCount($property->getId());

                for ($position = 1; $position <= $imagesCount; ++$position) {
                    $imageName = $this->getImageName(
                        propertyId: $property->getId(),
                        position: $position
                    );

                    $temporaryImagePath = $this->createPlaceholderImage(
                        tempDirectory: $tempDirectory,
                        propertyId: $property->getId(),
                        position: $position,
                        imageName: $imageName
                    );

                    $temporaryImagePaths[] = $temporaryImagePath;

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

                    if ($filesystem->exists($temporaryImagePath)) {
                        $propertyImage->setImageSize(filesize($temporaryImagePath) ?: null);
                    }

                    $manager->persist($propertyImage);

                    $propertyImages[] = $propertyImage;
                }

                /*
                 * Important :
                 * On flush après toutes les images du bien.
                 * Cela permet à VichUploader de traiter les UploadedFile
                 * sans faire un flush très lent image par image.
                 */
                $manager->flush();

                foreach ($propertyImages as $propertyImage) {
                    $manager->detach($propertyImage);
                }

                foreach ($temporaryImagePaths as $temporaryImagePath) {
                    if ($filesystem->exists($temporaryImagePath)) {
                        $filesystem->remove($temporaryImagePath);
                    }
                }

                unset($propertyImages, $temporaryImagePaths);

                gc_collect_cycles();
            }
        } finally {
            if ($filesystem->exists($tempDirectory)) {
                $filesystem->remove($tempDirectory);
            }
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
            'property-%06d-image-%02d.jpg',
            $propertyId,
            $position
        );
    }

    private function createPlaceholderImage(
        string $tempDirectory,
        int $propertyId,
        int $position,
        string $imageName,
    ): string {
        /*
         * 80% horizontal / 20% vertical.
         */
        $isPortrait = (($propertyId + $position) % 5) === 0;

        $width = $isPortrait
            ? self::IMAGE_PORTRAIT_WIDTH
            : self::IMAGE_LANDSCAPE_WIDTH;

        $height = $isPortrait
            ? self::IMAGE_PORTRAIT_HEIGHT
            : self::IMAGE_LANDSCAPE_HEIGHT;

        $imagePath = $tempDirectory.'/'.$imageName;

        $image = imagecreatetruecolor($width, $height);

        if (false === $image) {
            throw new \RuntimeException('Impossible de créer une image GD.');
        }

        $backgroundColor = $this->getBackgroundColor($image, $propertyId, $position);
        $primaryTextColor = imagecolorallocate($image, 45, 45, 45);
        $secondaryTextColor = imagecolorallocate($image, 90, 90, 90);
        $whiteColor = imagecolorallocate($image, 255, 255, 255);

        imagefilledrectangle($image, 0, 0, $width, $height, $backgroundColor);

        /*
         * Bande blanche en bas.
         */
        imagefilledrectangle($image, 0, $height - 170, $width, $height, $whiteColor);

        /*
         * Lignes décoratives.
         */
        for ($i = 0; $i < 8; ++$i) {
            $lineColor = imagecolorallocate(
                $image,
                min(255, 160 + ($i * 8)),
                min(255, 160 + ($i * 8)),
                min(255, 160 + ($i * 8))
            );

            imageline(
                $image,
                0,
                80 + ($i * 90),
                $width,
                30 + ($i * 120),
                $lineColor
            );
        }

        imagestring($image, 5, 40, $height - 135, 'Boolts Property', $primaryTextColor);

        imagestring(
            $image,
            4,
            40,
            $height - 105,
            \sprintf('Bien #%d - Image #%d', $propertyId, $position),
            $secondaryTextColor
        );

        imagestring(
            $image,
            4,
            40,
            $height - 80,
            \sprintf('%dx%d', $width, $height),
            $secondaryTextColor
        );

        imagejpeg($image, $imagePath, 85);
        imagedestroy($image);

        return $imagePath;
    }

    private function getBackgroundColor(\GdImage $image, int $propertyId, int $position): int
    {
        $colors = [
            [226, 232, 240],
            [221, 214, 254],
            [219, 234, 254],
            [220, 252, 231],
            [254, 243, 199],
            [255, 228, 230],
            [224, 242, 254],
            [237, 233, 254],
        ];

        $color = $colors[($propertyId + $position) % \count($colors)];

        return imagecolorallocate($image, $color[0], $color[1], $color[2]);
    }

    public function getDependencies(): array
    {
        return [
            PropertyFixtures::class,
        ];
    }
}
