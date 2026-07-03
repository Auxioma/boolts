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

use App\Entity\Property;
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
    private const TEMP_DIRECTORY = 'var/fixtures/property-images';

    private const PROPERTY_IMAGES_MIN = 3;
    private const PROPERTY_IMAGES_MAX = 6;

    private const IMAGE_LANDSCAPE_WIDTH = 1200;
    private const IMAGE_LANDSCAPE_HEIGHT = 800;

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
            throw new \RuntimeException("L'extension PHP GD est obligatoire pour générer les images.");
        }

        $filesystem = new Filesystem();

        $projectDir = $this->kernel->getProjectDir();
        $tempDirectory = $projectDir.\DIRECTORY_SEPARATOR.self::TEMP_DIRECTORY;

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

                $propertyId = (int) $property->getId();

                /*
                 * On supprime d'abord les anciennes images en base.
                 * Cela évite d'avoir des doublons si tu relances les fixtures.
                 */
                $this->removeExistingImages($manager, $property);
                $manager->flush();

                $temporaryImagePaths = [];
                $createdPropertyImages = [];

                try {
                    $imagesCount = $this->getImagesCount($propertyId);

                    for ($position = 1; $position <= $imagesCount; ++$position) {
                        $imageName = $this->getImageName(
                            propertyId: $propertyId,
                            position: $position
                        );

                        $temporaryImagePath = $this->createPropertyImage(
                            tempDirectory: $tempDirectory,
                            propertyId: $propertyId,
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

                        $createdPropertyImages[] = $propertyImage;
                    }

                    /*
                     * Important :
                     * Le flush doit être fait après la création des UploadedFile.
                     * VichUploader va déplacer les fichiers vers le dossier configuré.
                     */
                    $manager->flush();

                    foreach ($createdPropertyImages as $propertyImage) {
                        $manager->detach($propertyImage);
                    }
                } finally {
                    /*
                     * On nettoie les fichiers temporaires qui n'auraient pas été déplacés.
                     */
                    foreach ($temporaryImagePaths as $temporaryImagePath) {
                        if ($filesystem->exists($temporaryImagePath)) {
                            $filesystem->remove($temporaryImagePath);
                        }
                    }

                    unset($temporaryImagePaths, $createdPropertyImages);

                    gc_collect_cycles();
                }

                $manager->detach($property);
            }
        } finally {
            if ($filesystem->exists($tempDirectory)) {
                $filesystem->remove($tempDirectory);
            }
        }
    }

    private function removeExistingImages(ObjectManager $manager, Property $property): void
    {
        $existingImages = $manager
            ->getRepository(PropertyImage::class)
            ->findBy([
                'property' => $property,
            ]);

        foreach ($existingImages as $existingImage) {
            $manager->remove($existingImage);
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

    private function createPropertyImage(
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

        $imagePath = $tempDirectory.\DIRECTORY_SEPARATOR.$imageName;

        $image = imagecreatetruecolor($width, $height);

        if (false === $image) {
            throw new \RuntimeException('Impossible de créer une image GD.');
        }

        try {
            if (\function_exists('imageantialias')) {
                imageantialias($image, true);
            }

            $palette = $this->getPalette($propertyId, $position);

            $wallColor = $this->allocateColor($image, $palette['wall']);
            $floorColor = $this->allocateColor($image, $palette['floor']);
            $windowColor = $this->allocateColor($image, $palette['window']);
            $furnitureColor = $this->allocateColor($image, $palette['furniture']);
            $shadowColor = $this->allocateColor($image, [70, 70, 70]);
            $whiteColor = $this->allocateColor($image, [255, 255, 255]);
            $textColor = $this->allocateColor($image, [45, 45, 45]);
            $mutedTextColor = $this->allocateColor($image, [95, 95, 95]);

            /*
             * Mur.
             */
            imagefilledrectangle($image, 0, 0, $width, $height, $wallColor);

            /*
             * Sol en perspective.
             */
            imagefilledpolygon(
                $image,
                [
                    0,
                    (int) ($height * 0.62),
                    $width,
                    (int) ($height * 0.54),
                    $width,
                    $height,
                    0,
                    $height,
                ],
                4,
                $floorColor
            );

            /*
             * Fenêtre.
             */
            $windowX = (int) ($width * 0.12);
            $windowY = (int) ($height * 0.12);
            $windowWidth = (int) ($width * 0.28);
            $windowHeight = (int) ($height * 0.28);

            imagefilledrectangle(
                $image,
                $windowX,
                $windowY,
                $windowX + $windowWidth,
                $windowY + $windowHeight,
                $whiteColor
            );

            imagefilledrectangle(
                $image,
                $windowX + 12,
                $windowY + 12,
                $windowX + $windowWidth - 12,
                $windowY + $windowHeight - 12,
                $windowColor
            );

            imageline(
                $image,
                $windowX + (int) ($windowWidth / 2),
                $windowY + 12,
                $windowX + (int) ($windowWidth / 2),
                $windowY + $windowHeight - 12,
                $whiteColor
            );

            imageline(
                $image,
                $windowX + 12,
                $windowY + (int) ($windowHeight / 2),
                $windowX + $windowWidth - 12,
                $windowY + (int) ($windowHeight / 2),
                $whiteColor
            );

            /*
             * Cadre décoratif.
             */
            $frameX = (int) ($width * 0.58);
            $frameY = (int) ($height * 0.16);
            $frameWidth = (int) ($width * 0.22);
            $frameHeight = (int) ($height * 0.18);

            imagefilledrectangle(
                $image,
                $frameX,
                $frameY,
                $frameX + $frameWidth,
                $frameY + $frameHeight,
                $whiteColor
            );

            imagefilledrectangle(
                $image,
                $frameX + 10,
                $frameY + 10,
                $frameX + $frameWidth - 10,
                $frameY + $frameHeight - 10,
                $this->allocateColor($image, $palette['decor'])
            );

            /*
             * Mobilier simple selon la position.
             */
            $this->drawFurniture(
                image: $image,
                width: $width,
                height: $height,
                position: $position,
                furnitureColor: $furnitureColor,
                shadowColor: $shadowColor,
                whiteColor: $whiteColor
            );

            /*
             * Bande blanche en bas pour identifier facilement l'image fixture.
             */
            $labelHeight = 135;

            imagefilledrectangle(
                $image,
                0,
                $height - $labelHeight,
                $width,
                $height,
                $whiteColor
            );

            imagestring(
                $image,
                5,
                35,
                $height - 105,
                'Boolts Property',
                $textColor
            );

            imagestring(
                $image,
                4,
                35,
                $height - 78,
                \sprintf('Bien #%d - Image #%d', $propertyId, $position),
                $mutedTextColor
            );

            imagestring(
                $image,
                4,
                35,
                $height - 53,
                \sprintf('%dx%d - Fixture image', $width, $height),
                $mutedTextColor
            );

            if (!imagejpeg($image, $imagePath, 88)) {
                throw new \RuntimeException(\sprintf(
                    "Impossible d'écrire l'image : %s",
                    $imagePath
                ));
            }
        } finally {
            imagedestroy($image);
        }

        return $imagePath;
    }

    private function drawFurniture(
        \GdImage $image,
        int $width,
        int $height,
        int $position,
        int $furnitureColor,
        int $shadowColor,
        int $whiteColor,
    ): void {
        $type = $position % 4;

        /*
         * Ombre générale.
         */
        imagefilledellipse(
            $image,
            (int) ($width * 0.55),
            (int) ($height * 0.74),
            (int) ($width * 0.45),
            (int) ($height * 0.08),
            $shadowColor
        );

        if (0 === $type) {
            /*
             * Canapé.
             */
            imagefilledrectangle(
                $image,
                (int) ($width * 0.35),
                (int) ($height * 0.52),
                (int) ($width * 0.78),
                (int) ($height * 0.68),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.32),
                (int) ($height * 0.60),
                (int) ($width * 0.82),
                (int) ($height * 0.75),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.38),
                (int) ($height * 0.55),
                (int) ($width * 0.52),
                (int) ($height * 0.64),
                $whiteColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.58),
                (int) ($height * 0.55),
                (int) ($width * 0.72),
                (int) ($height * 0.64),
                $whiteColor
            );

            return;
        }

        if (1 === $type) {
            /*
             * Lit.
             */
            imagefilledrectangle(
                $image,
                (int) ($width * 0.30),
                (int) ($height * 0.50),
                (int) ($width * 0.82),
                (int) ($height * 0.73),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.34),
                (int) ($height * 0.53),
                (int) ($width * 0.78),
                (int) ($height * 0.65),
                $whiteColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.34),
                (int) ($height * 0.48),
                (int) ($width * 0.52),
                (int) ($height * 0.57),
                $whiteColor
            );

            return;
        }

        if (2 === $type) {
            /*
             * Table + chaises.
             */
            imagefilledrectangle(
                $image,
                (int) ($width * 0.40),
                (int) ($height * 0.55),
                (int) ($width * 0.72),
                (int) ($height * 0.61),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.45),
                (int) ($height * 0.61),
                (int) ($width * 0.48),
                (int) ($height * 0.75),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.64),
                (int) ($height * 0.61),
                (int) ($width * 0.67),
                (int) ($height * 0.75),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.30),
                (int) ($height * 0.56),
                (int) ($width * 0.38),
                (int) ($height * 0.72),
                $furnitureColor
            );

            imagefilledrectangle(
                $image,
                (int) ($width * 0.75),
                (int) ($height * 0.56),
                (int) ($width * 0.83),
                (int) ($height * 0.72),
                $furnitureColor
            );

            return;
        }

        /*
         * Cuisine / meuble bas.
         */
        imagefilledrectangle(
            $image,
            (int) ($width * 0.34),
            (int) ($height * 0.50),
            (int) ($width * 0.82),
            (int) ($height * 0.73),
            $furnitureColor
        );

        imagefilledrectangle(
            $image,
            (int) ($width * 0.34),
            (int) ($height * 0.48),
            (int) ($width * 0.82),
            (int) ($height * 0.52),
            $whiteColor
        );

        for ($i = 0; $i < 4; ++$i) {
            $x = (int) ($width * 0.38) + ($i * (int) ($width * 0.10));

            imagefilledrectangle(
                $image,
                $x,
                (int) ($height * 0.56),
                $x + (int) ($width * 0.07),
                (int) ($height * 0.68),
                $whiteColor
            );
        }
    }

    /**
     * @return array{
     *     wall: array{0:int, 1:int, 2:int},
     *     floor: array{0:int, 1:int, 2:int},
     *     window: array{0:int, 1:int, 2:int},
     *     furniture: array{0:int, 1:int, 2:int},
     *     decor: array{0:int, 1:int, 2:int}
     * }
     */
    private function getPalette(int $propertyId, int $position): array
    {
        $palettes = [
            [
                'wall' => [226, 232, 240],
                'floor' => [203, 213, 225],
                'window' => [186, 230, 253],
                'furniture' => [100, 116, 139],
                'decor' => [221, 214, 254],
            ],
            [
                'wall' => [245, 245, 244],
                'floor' => [214, 211, 209],
                'window' => [191, 219, 254],
                'furniture' => [120, 113, 108],
                'decor' => [254, 243, 199],
            ],
            [
                'wall' => [239, 246, 255],
                'floor' => [219, 234, 254],
                'window' => [125, 211, 252],
                'furniture' => [30, 64, 175],
                'decor' => [224, 242, 254],
            ],
            [
                'wall' => [240, 253, 244],
                'floor' => [220, 252, 231],
                'window' => [186, 230, 253],
                'furniture' => [22, 101, 52],
                'decor' => [254, 249, 195],
            ],
            [
                'wall' => [255, 247, 237],
                'floor' => [254, 215, 170],
                'window' => [186, 230, 253],
                'furniture' => [154, 52, 18],
                'decor' => [255, 228, 230],
            ],
        ];

        return $palettes[($propertyId + $position) % \count($palettes)];
    }

    /**
     * @param array{0:int, 1:int, 2:int} $rgb
     */
    private function allocateColor(\GdImage $image, array $rgb): int
    {
        return imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);
    }

    public function getDependencies(): array
    {
        return [
            PropertyFixtures::class,
        ];
    }
}