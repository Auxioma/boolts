<?php

declare(strict_types=1);

namespace App\Command;

use App\DataFixtures\PropertyFixtures;
use App\DataFixtures\RealEstate\PropertyLocationCatalog;
use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\PropertyTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:fixtures:validate-properties',
    description: 'Contrôle les villes, relations et galeries des fixtures immobilières.'
)]
final class ValidatePropertyFixturesCommand extends Command
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly string $projectDir,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $errors = [];
        $properties = $this->entityManager->getRepository(Property::class)->findAll();
        $expectedCities = PropertyLocationCatalog::all();
        $expectedTotal = count($expectedCities) * PropertyFixtures::PROPERTIES_PER_CITY;

        $io->writeln(sprintf('Biens analysés : %d', count($properties)));
        $io->writeln(sprintf('Biens attendus : %d', $expectedTotal));

        if (count($properties) !== $expectedTotal) {
            $errors[] = sprintf('Nombre total de biens invalide : %d au lieu de %d.', count($properties), $expectedTotal);
        }

        $cityCounts = $this->getCityCounts();
        foreach ($expectedCities as $city) {
            $key = $city['country'].'|'.$city['ville'];
            $count = $cityCounts[$key] ?? 0;

            if ($count !== PropertyFixtures::PROPERTIES_PER_CITY) {
                $errors[] = sprintf('%s, %s : %d biens au lieu de %d.', $city['ville'], $city['country'], $count, PropertyFixtures::PROPERTIES_PER_CITY);
            }
        }

        $imageCounts = $this->getImageCounts();
        $checkedFiles = [];

        foreach ($properties as $property) {
            $propertyId = $property->getId();
            $imageCount = null === $propertyId ? 0 : ($imageCounts[$propertyId] ?? 0);

            if (null === $property->getTypeBien() || null === $property->getTypeTransaction() || null === $property->getUser()) {
                $errors[] = sprintf('Property #%s : relation obligatoire absente.', $propertyId ?? 'nouveau');
            }

            if (null === $property->getVille() || null === $property->getPays() || null === $property->getLatitude() || null === $property->getLongitude()) {
                $errors[] = sprintf('Property #%s : localisation incomplète.', $propertyId ?? 'nouveau');
            }

            if ($imageCount < 5 || $imageCount > 12) {
                $errors[] = sprintf('Property #%s : %d images, attendu entre 5 et 12.', $propertyId ?? 'nouveau', $imageCount);
            }
        }

        foreach ($this->entityManager->getRepository(PropertyImage::class)->findAll() as $image) {
            $name = $image->getImageName();
            if (null === $name || '' === $name) {
                $errors[] = sprintf('PropertyImage #%s : nom de fichier absent.', $image->getId() ?? 'nouvelle');

                continue;
            }

            if (isset($checkedFiles[$name])) {
                continue;
            }

            $checkedFiles[$name] = true;
            $path = $this->projectDir.'/public/properties/'.ltrim($name, '/');

            if (!is_file($path) || false === @getimagesize($path)) {
                $errors[] = sprintf('Image immobilière introuvable ou invalide : %s', $name);
            }
        }

        $countrySummary = $this->getCountrySummary();
        foreach ($countrySummary as $country => $count) {
            $io->writeln(sprintf('%s : %d biens', $country, $count));
        }

        $io->writeln(sprintf('Images analysées : %d', count($checkedFiles)));

        if ([] !== $errors) {
            foreach (array_slice($errors, 0, 50) as $error) {
                $io->error($error);
            }

            if (count($errors) > 50) {
                $io->error(sprintf('%d anomalies supplémentaires non affichées.', count($errors) - 50));
            }

            return Command::FAILURE;
        }

        $io->success('Fixtures immobilières valides : villes, relations et galeries conformes.');

        return Command::SUCCESS;
    }

    /** @return array<string, int> */
    private function getCityCounts(): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('pt.pays AS country, pt.ville AS city, COUNT(pt.id) AS total')
            ->from(PropertyTranslation::class, 'pt')
            ->where('pt.locale = :locale')
            ->setParameter('locale', 'fr')
            ->groupBy('pt.pays, pt.ville')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[$row['country'].'|'.$row['city']] = (int) $row['total'];
        }

        return $counts;
    }

    /** @return array<int, int> */
    private function getImageCounts(): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('IDENTITY(pi.property) AS propertyId, COUNT(pi.id) AS total')
            ->from(PropertyImage::class, 'pi')
            ->groupBy('pi.property')
            ->getQuery()
            ->getArrayResult();

        $counts = [];
        foreach ($rows as $row) {
            $counts[(int) $row['propertyId']] = (int) $row['total'];
        }

        return $counts;
    }

    /** @return array<string, int> */
    private function getCountrySummary(): array
    {
        $rows = $this->entityManager->createQueryBuilder()
            ->select('pt.pays AS country, COUNT(pt.id) AS total')
            ->from(PropertyTranslation::class, 'pt')
            ->where('pt.locale = :locale')
            ->setParameter('locale', 'fr')
            ->groupBy('pt.pays')
            ->getQuery()
            ->getArrayResult();

        $summary = [];
        foreach ($rows as $row) {
            $summary[$row['country']] = (int) $row['total'];
        }

        return $summary;
    }
}
