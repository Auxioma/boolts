<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Repository\PropertyRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Lie des galeries locales existantes aux biens, sans téléchargement.
 *
 * La source de fixture est séparée du dossier d'upload Vich, car la purge des
 * anciennes entités PropertyImage peut supprimer les fichiers de public/properties
 * avec delete_on_remove. Les images utilisées sont donc recopiées dans la
 * destination Vich juste avant d'être référencées.
 */
final class PropertyImageFixtures extends Fixture implements DependentFixtureInterface
{
    private const MIN_IMAGES_PER_PROPERTY = 5;
    private const MAX_IMAGES_PER_PROPERTY = 12;
    private const MAX_PROPERTIES_PER_SOURCE_SERIES = 5;
    private const FLUSH_EVERY_PROPERTIES = 100;
    private const UPLOAD_DIRECTORY = 'public/properties';
    private const SOURCE_DIRECTORIES = [
        'public/images/biens',
        self::UPLOAD_DIRECTORY,
    ];

    public function __construct(
        private readonly KernelInterface $kernel,
        private readonly PropertyRepository $propertyRepository,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        /** @var list<Property> $properties */
        $properties = $this->propertyRepository->findAll();

        if ([] === $properties) {
            throw new \RuntimeException("Aucun bien immobilier trouvé. Lance d'abord PropertyFixtures.");
        }

        $series = $this->getAvailableImageSeries(count($properties));

        if ([] === $series) {
            throw new \RuntimeException(
                'Aucune galerie locale valide contenant au moins cinq images n’est disponible dans public/images/biens ou public/properties.'
            );
        }

        foreach ($properties as $propertyIndex => $property) {
            $seriesIndex = intdiv($propertyIndex, self::MAX_PROPERTIES_PER_SOURCE_SERIES) % count($series);
            $gallery = $series[$seriesIndex];
            $imagesCount = min(
                self::MIN_IMAGES_PER_PROPERTY + ($propertyIndex % (self::MAX_IMAGES_PER_PROPERTY - self::MIN_IMAGES_PER_PROPERTY + 1)),
                count($gallery)
            );

            if ($imagesCount < self::MIN_IMAGES_PER_PROPERTY) {
                throw new \RuntimeException(sprintf(
                    'La galerie locale sélectionnée pour le bien %d contient moins de cinq images.',
                    $propertyIndex + 1
                ));
            }

            foreach (array_slice($gallery, 0, $imagesCount) as $position => $image) {
                $imageName = $this->ensureImageExistsInUploadDirectory($image);

                $propertyImage = new PropertyImage();
                $propertyImage
                    ->setProperty($property)
                    ->setPosition((string) ($position + 1))
                    ->setImageName($imageName)
                    ->setImageSize(filesize($this->getUploadDirectory().'/'.$imageName) ?: $image['size']);

                $manager->persist($propertyImage);
            }

            if (0 === ($propertyIndex + 1) % self::FLUSH_EVERY_PROPERTIES) {
                $manager->flush();
            }
        }

        $manager->flush();
    }

    /**
     * @return list<list<array{name: string, sourcePath: string, size: int}>>
     */
    private function getAvailableImageSeries(int $propertiesCount): array
    {
        $requiredSeries = (int) ceil($propertiesCount / self::MAX_PROPERTIES_PER_SOURCE_SERIES);

        /** @var array<string, array<int, array{name: string, sourcePath: string, size: int}>> $series */
        $series = [];
        /** @var array<string, true> $completeSeries */
        $completeSeries = [];

        foreach (self::SOURCE_DIRECTORIES as $sourceDirectory) {
            $absoluteSourceDirectory = $this->kernel->getProjectDir().'/'.$sourceDirectory;

            if (!is_dir($absoluteSourceDirectory)) {
                continue;
            }

            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($absoluteSourceDirectory, \FilesystemIterator::SKIP_DOTS)
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                if (!$file->isFile() || !in_array(mb_strtolower($file->getExtension()), ['jpeg', 'jpg', 'png', 'webp'], true)) {
                    continue;
                }

                $size = $file->getSize();
                $imageInformation = @getimagesize($file->getPathname());
                if (false === $size || $size <= 0 || false === $imageInformation) {
                    continue;
                }

                if (1_200 > (int) $imageInformation[0] || 800 > (int) $imageInformation[1]) {
                    continue;
                }

                if (1 !== preg_match('/^(property-\d{6})-image-(\d{2})-/i', $file->getFilename(), $matches)) {
                    continue;
                }

                $seriesKey = mb_strtolower($matches[1]);
                $sourcePosition = (int) $matches[2];
                $relativePath = str_replace('\\', '/', substr($file->getPathname(), strlen($absoluteSourceDirectory) + 1));

                if (!isset($series[$seriesKey][$sourcePosition])) {
                    $series[$seriesKey][$sourcePosition] = [
                        'name' => $relativePath,
                        'sourcePath' => $file->getPathname(),
                        'size' => $size,
                    ];

                    if (count($series[$seriesKey]) >= self::MIN_IMAGES_PER_PROPERTY) {
                        $completeSeries[$seriesKey] = true;

                        if (count($completeSeries) >= $requiredSeries) {
                            break 2;
                        }
                    }
                }
            }
        }

        ksort($series);
        $galleries = [];

        foreach ($series as $gallery) {
            ksort($gallery);
            if (count($gallery) >= self::MIN_IMAGES_PER_PROPERTY) {
                $galleries[] = array_values($gallery);
            }
        }

        return $galleries;
    }

    /**
     * @param array{name: string, sourcePath: string, size: int} $image
     */
    private function ensureImageExistsInUploadDirectory(array $image): string
    {
        $uploadDirectory = $this->getUploadDirectory();
        $imageName = ltrim(str_replace('\\', '/', $image['name']), '/');
        $targetPath = $uploadDirectory.'/'.$imageName;

        if (!is_dir(dirname($targetPath)) && !mkdir(dirname($targetPath), 0775, true) && !is_dir(dirname($targetPath))) {
            throw new \RuntimeException(sprintf('Impossible de créer le dossier de destination "%s".', dirname($targetPath)));
        }

        if (is_file($targetPath) && false !== @getimagesize($targetPath)) {
            return $imageName;
        }

        if (!copy($image['sourcePath'], $targetPath)) {
            throw new \RuntimeException(sprintf(
                'Impossible de copier l’image de fixture "%s" vers "%s".',
                $image['sourcePath'],
                $targetPath
            ));
        }

        return $imageName;
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
