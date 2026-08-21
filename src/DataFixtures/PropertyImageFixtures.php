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

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Génère les fichiers image des biens directement dans le dossier d'upload Vich.
 */
final class PropertyImageFixtures extends Fixture implements DependentFixtureInterface
{
    private const MIN_IMAGES_PER_PROPERTY = 5;
    private const MAX_IMAGES_PER_PROPERTY = 12;
    private const MAX_PROPERTIES_PER_GENERATED_SET = 5;
    private const UPLOAD_DIRECTORY = 'public/properties';
    private const IMAGE_WIDTH = 1200;
    private const IMAGE_HEIGHT = 800;
    private const JPEG_QUALITY = 82;

    private const PALETTES = [
        [[236, 232, 221], [173, 138, 94], [61, 96, 112], [140, 190, 214]],
        [[229, 234, 232], [124, 151, 132], [135, 72, 63], [154, 198, 222]],
        [[238, 228, 214], [184, 156, 114], [52, 84, 74], [127, 183, 210]],
        [[228, 230, 235], [142, 142, 154], [171, 123, 65], [150, 196, 225]],
        [[235, 231, 224], [103, 133, 151], [96, 70, 54], [138, 185, 212]],
    ];

    public function __construct(
        private readonly KernelInterface $kernel,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        if (!$manager instanceof EntityManagerInterface) {
            throw new \LogicException('Le manager doit être une instance de EntityManagerInterface.');
        }

        $nativeConnection = $manager->getConnection()->getNativeConnection();

        if (!$nativeConnection instanceof \PDO) {
            throw new \LogicException('La fixture des images nécessite une connexion PDO native.');
        }

        $propertyStatement = $nativeConnection->query('SELECT id FROM property ORDER BY id ASC');

        if (false === $propertyStatement) {
            throw new \RuntimeException('Impossible de récupérer les biens immobiliers pour générer leurs images.');
        }

        /** @var list<int|string> $propertyIds */
        $propertyIds = $propertyStatement->fetchAll(\PDO::FETCH_COLUMN);

        if ([] === $propertyIds) {
            throw new \RuntimeException("Aucun bien immobilier trouvé. Lance d'abord PropertyFixtures.");
        }

        $statement = $nativeConnection->prepare(
            'INSERT INTO property_image (property_id, image_name, image_size, position, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?)'
        );

        if (false === $statement) {
            throw new \RuntimeException('Impossible de préparer l’insertion des images de biens.');
        }

        $ownsTransaction = !$nativeConnection->inTransaction();

        if ($ownsTransaction) {
            $nativeConnection->beginTransaction();
        }

        try {
            foreach ($propertyIds as $propertyIndex => $propertyId) {
                $generatedSet = intdiv($propertyIndex, self::MAX_PROPERTIES_PER_GENERATED_SET) + 1;
                $imagesCount = self::MIN_IMAGES_PER_PROPERTY + ($propertyIndex % (self::MAX_IMAGES_PER_PROPERTY - self::MIN_IMAGES_PER_PROPERTY + 1));

                for ($position = 1; $position <= $imagesCount; ++$position) {
                    $image = $this->createGeneratedImage($generatedSet, $position);
                    $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');

                    $statement->execute([
                        $propertyId,
                        $image['name'],
                        $image['size'],
                        (string) $position,
                        $now,
                        $now,
                    ]);
                }
            }

            if ($ownsTransaction) {
                $nativeConnection->commit();
            }
        } catch (\Throwable $exception) {
            if ($ownsTransaction && $nativeConnection->inTransaction()) {
                $nativeConnection->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * @return array{name: string, size: int}
     */
    private function createGeneratedImage(int $generatedSet, int $position): array
    {
        $uploadDirectory = $this->getUploadDirectory();

        if (!is_dir($uploadDirectory) && !mkdir($uploadDirectory, 0775, true) && !is_dir($uploadDirectory)) {
            throw new \RuntimeException(\sprintf('Impossible de créer le dossier de destination "%s".', $uploadDirectory));
        }

        $imageName = \sprintf('property-%06d-image-%02d-fixture.jpg', $generatedSet, $position);
        $targetPath = $uploadDirectory.'/'.$imageName;

        if (!is_file($targetPath) || false === @getimagesize($targetPath)) {
            $this->drawGeneratedImage($targetPath, $generatedSet, $position);
        }

        $size = filesize($targetPath);

        if (false === $size || $size <= 0) {
            throw new \RuntimeException(\sprintf('L’image générée "%s" est vide ou inaccessible.', $targetPath));
        }

        return [
            'name' => $imageName,
            'size' => $size,
        ];
    }

    private function drawGeneratedImage(string $targetPath, int $generatedSet, int $position): void
    {
        if (!\function_exists('imagecreatetruecolor')) {
            throw new \RuntimeException('L’extension PHP GD est requise pour générer les images de fixtures.');
        }

        $image = imagecreatetruecolor(self::IMAGE_WIDTH, self::IMAGE_HEIGHT);

        if (!$image instanceof \GdImage) {
            throw new \RuntimeException('Impossible de créer une image GD pour les fixtures.');
        }

        $palette = self::PALETTES[($generatedSet + $position) % \count(self::PALETTES)];
        [$wallRgb, $floorRgb, $accentRgb, $skyRgb] = $palette;

        $wall = $this->allocateColor($image, $wallRgb);
        $wallShadow = $this->allocateColor($image, $this->adjustColor($wallRgb, -18));
        $floor = $this->allocateColor($image, $floorRgb);
        $floorDark = $this->allocateColor($image, $this->adjustColor($floorRgb, -35));
        $accent = $this->allocateColor($image, $accentRgb);
        $accentLight = $this->allocateColor($image, $this->adjustColor($accentRgb, 45));
        $sky = $this->allocateColor($image, $skyRgb);
        $white = $this->allocateColor($image, [250, 250, 246]);
        $glass = $this->allocateColor($image, $this->adjustColor($skyRgb, 28));
        $line = $this->allocateColor($image, [52, 58, 61]);

        imagefilledrectangle($image, 0, 0, self::IMAGE_WIDTH, 520, $wall);
        imagefilledpolygon($image, [0, self::IMAGE_HEIGHT, self::IMAGE_WIDTH, self::IMAGE_HEIGHT, self::IMAGE_WIDTH, 500, 0, 565], $floor);

        for ($x = -120; $x < self::IMAGE_WIDTH; $x += 140) {
            imagefilledpolygon($image, [$x, self::IMAGE_HEIGHT, $x + 54, self::IMAGE_HEIGHT, $x + 280, 520, $x + 226, 520], $floorDark);
        }

        $windowX = 760 + (($generatedSet + $position) % 4) * 18;
        imagefilledrectangle($image, $windowX, 95, $windowX + 285, 315, $white);
        imagefilledrectangle($image, $windowX + 14, 109, $windowX + 271, 301, $sky);
        imagefilledrectangle($image, $windowX + 130, 109, $windowX + 142, 301, $white);
        imagefilledrectangle($image, $windowX + 14, 202, $windowX + 271, 214, $white);
        imagefilledellipse($image, $windowX + 226, 146, 54, 54, $glass);

        imagefilledrectangle($image, 145, 125, 475, 340, $white);
        imagefilledrectangle($image, 168, 148, 452, 317, $accentLight);
        imagefilledrectangle($image, 198, 184, 422, 287, $accent);

        $furnitureVariant = ($generatedSet + $position) % 3;
        if (0 === $furnitureVariant) {
            imagefilledrectangle($image, 230, 455, 720, 610, $accent);
            imagefilledrectangle($image, 190, 525, 760, 675, $accent);
            imagefilledrectangle($image, 250, 418, 390, 510, $accentLight);
            imagefilledrectangle($image, 565, 418, 705, 510, $accentLight);
        } elseif (1 === $furnitureVariant) {
            imagefilledrectangle($image, 155, 455, 725, 630, $white);
            imagefilledrectangle($image, 185, 505, 755, 685, $accentLight);
            imagefilledrectangle($image, 160, 420, 355, 505, $accent);
            imagefilledrectangle($image, 385, 420, 580, 505, $accent);
        } else {
            imagefilledrectangle($image, 210, 415, 655, 600, $accentLight);
            imagefilledrectangle($image, 260, 470, 705, 650, $accent);
            imagefilledrectangle($image, 145, 505, 260, 665, $white);
            imagefilledrectangle($image, 650, 505, 765, 665, $white);
        }

        imagefilledrectangle($image, 850, 515, 1030, 548, $wallShadow);
        imagefilledrectangle($image, 905, 548, 975, 690, $wallShadow);
        imagefilledellipse($image, 940, 485, 95, 95, $accentLight);

        imagestring($image, 3, 32, 30, \sprintf('BOOLTS %06d-%02d', $generatedSet, $position), $line);

        if (!imagejpeg($image, $targetPath, self::JPEG_QUALITY)) {
            throw new \RuntimeException(\sprintf('Impossible d’écrire l’image générée "%s".', $targetPath));
        }
    }

    /**
     * @param list<int> $rgb
     */
    private function allocateColor(\GdImage $image, array $rgb): int
    {
        $color = imagecolorallocate($image, $rgb[0], $rgb[1], $rgb[2]);

        if (false === $color) {
            throw new \RuntimeException('Impossible d’allouer une couleur pour une image de fixture.');
        }

        return $color;
    }

    /**
     * @param list<int> $rgb
     *
     * @return list<int>
     */
    private function adjustColor(array $rgb, int $delta): array
    {
        return [
            max(0, min(255, $rgb[0] + $delta)),
            max(0, min(255, $rgb[1] + $delta)),
            max(0, min(255, $rgb[2] + $delta)),
        ];
    }

    private function getUploadDirectory(): string
    {
        return $this->kernel->getProjectDir().'/'.self::UPLOAD_DIRECTORY;
    }

    public function getDependencies(): array
    {
        return [PropertyFixtures::class];
    }
}
