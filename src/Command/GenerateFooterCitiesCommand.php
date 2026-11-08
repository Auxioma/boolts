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

namespace App\Command;

use App\Repository\PaysRepository;
use App\Service\FooterCitiesProvider;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Génère config/footer_cities.json : pour chaque pays de la table `pays`,
 * les 18 plus grandes villes (OpenStreetMap / Overpass) avec leur nom dans chaque langue.
 *
 *   php bin/console app:footer-cities:generate                 → pays absents du fichier
 *   php bin/console app:footer-cities:generate --force         → tous les pays
 *   php bin/console app:footer-cities:generate -c FR -c DE     → seulement ces pays
 *   php bin/console app:footer-cities:generate --locales=fr,en,es
 *
 * Le fichier est enregistré après chaque pays : la commande peut être interrompue et relancée.
 */
#[AsCommand(
    name: 'app:footer-cities:generate',
    description: 'Génère le fichier JSON multilingue des plus grandes villes par pays (footer).'
)]
final class GenerateFooterCitiesCommand extends Command
{
    /** Instances publiques Overpass, essayées dans l'ordre (elles sont souvent surchargées). */
    private const array OVERPASS_URLS = [
        'https://overpass-api.de/api/interpreter',
        'https://overpass.private.coffee/api/interpreter',
        'https://overpass.kumi.systems/api/interpreter',
    ];

    private const int PAUSE_BETWEEN_COUNTRIES = 2;

    public function __construct(
        private readonly PaysRepository $paysRepository,
        private readonly HttpClientInterface $httpClient,
        private readonly FooterCitiesProvider $footerCitiesProvider,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('country', 'c', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'Code(s) ISO des pays à générer (par défaut : tous les pays de la BDD).')
            ->addOption('locales', 'l', InputOption::VALUE_REQUIRED, 'Langues des noms de villes.', implode(',', FooterCitiesProvider::LOCALES))
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Régénère aussi les pays déjà présents dans le fichier.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $locales = array_values(array_filter(array_map('trim', explode(',', (string) $input->getOption('locales')))));
        $countryCodes = array_map('strtoupper', $input->getOption('country'));

        if ([] === $countryCodes) {
            $countryCodes = array_filter(array_map(
                static fn ($pays): ?string => $pays->getIso(),
                $this->paysRepository->findBy([], ['iso' => 'ASC']),
            ));
        }

        $filePath = $this->footerCitiesProvider->getFilePath();
        $data = $this->footerCitiesProvider->readFile();
        $force = $input->getOption('force') || [] !== $input->getOption('country');
        $failed = [];

        $io->title(\sprintf('Villes du footer — %d pays, langues : %s', \count($countryCodes), implode(', ', $locales)));

        foreach (array_values($countryCodes) as $index => $countryCode) {
            $prefix = \sprintf('[%d/%d] %s', $index + 1, \count($countryCodes), $countryCode);

            if (!$force && isset($data[$countryCode])) {
                $io->writeln($prefix.' déjà présent, ignoré.');

                continue;
            }

            $cities = $this->fetchCountry($countryCode, $locales, $io);

            if (null === $cities) {
                $failed[] = $countryCode;
                $io->writeln('<error>'.$prefix.' échec (Overpass indisponible).</error>');

                continue;
            }

            $data[$countryCode] = $cities;
            ksort($data);
            $this->writeFile($filePath, $data);

            $io->writeln(\sprintf(
                '%s %d villes : %s',
                $prefix,
                \count($cities),
                implode(', ', array_map(static fn (array $city): string => $city['fr'] ?? $city['default'], \array_slice($cities, 0, 5))).(\count($cities) > 5 ? '…' : ''),
            ));

            sleep(self::PAUSE_BETWEEN_COUNTRIES);
        }

        if ([] !== $failed) {
            $io->warning('Pays en échec, relancez la commande pour les reprendre : '.implode(', ', $failed));

            return Command::FAILURE;
        }

        $io->success('Fichier généré : '.$filePath);

        return Command::SUCCESS;
    }

    /**
     * Villes du pays (place=city), complétées par les place=town s'il y en a moins de 18.
     *
     * @param list<string> $locales
     *
     * @return list<array<string, string>>|null null si Overpass n'a pas répondu
     */
    private function fetchCountry(string $countryCode, array $locales, SymfonyStyle $io): ?array
    {
        $cities = $this->queryPlaces($countryCode, 'city', $locales, $io);

        if (null === $cities) {
            return null;
        }

        if (\count($cities) < FooterCitiesProvider::LIMIT) {
            $towns = $this->queryPlaces($countryCode, 'town', $locales, $io);

            if (null === $towns) {
                return null;
            }

            $cities += $towns;
        }

        uasort($cities, static fn (array $a, array $b): int => $b['population'] <=> $a['population']);

        return array_values(array_map(
            static fn (array $city): array => $city['names'],
            \array_slice($cities, 0, FooterCitiesProvider::LIMIT),
        ));
    }

    /**
     * @param list<string> $locales
     *
     * @return array<string, array{population: int, names: array<string, string>}>|null
     */
    private function queryPlaces(string $countryCode, string $place, array $locales, SymfonyStyle $io): ?array
    {
        $nameColumns = implode(',', array_map(static fn (string $locale): string => '"name:'.$locale.'"', $locales));

        // Sortie CSV limitée aux colonnes utiles (la sortie JSON complète est énorme).
        $query = <<<OVERPASS
            [out:csv(name,{$nameColumns},population;false;"\t")][timeout:180];
            area["ISO3166-1"="{$countryCode}"]["admin_level"="2"]->.country;
            node["place"="{$place}"]["population"](area.country);
            out tags;
            OVERPASS;

        foreach (self::OVERPASS_URLS as $url) {
            try {
                $content = $this->httpClient->request('POST', $url, [
                    'body' => ['data' => $query],
                    'headers' => ['User-Agent' => 'Boolts/1.0 (+https://boolts.com)'],
                    'timeout' => 200,
                    'max_duration' => 200,
                ])->getContent();
            } catch (\Throwable $exception) {
                $io->writeln(\sprintf('  <comment>%s : %s</comment>', parse_url($url, \PHP_URL_HOST), $exception->getMessage()), OutputInterface::VERBOSITY_VERBOSE);

                continue;
            }

            return $this->parseCsv($content, $locales);
        }

        return null;
    }

    /**
     * @param list<string> $locales
     *
     * @return array<string, array{population: int, names: array<string, string>}>
     */
    private function parseCsv(string $content, array $locales): array
    {
        $places = [];

        // Certaines réponses Overpass contiennent des octets UTF-8 invalides (json_encode échouerait).
        $content = mb_scrub($content, 'UTF-8');

        foreach (preg_split('/\R/', mb_trim($content)) ?: [] as $line) {
            $columns = explode("\t", $line);
            $name = mb_trim($columns[0] ?? '');
            $population = (int) preg_replace('/\D/', '', $columns[\count($locales) + 1] ?? '');

            if ('' === $name || $population <= 0) {
                continue;
            }

            $names = ['default' => $name];

            foreach ($locales as $index => $locale) {
                $localized = mb_trim($columns[$index + 1] ?? '');

                if ('' !== $localized) {
                    $names[$locale] = $localized;
                }
            }

            // Une même ville peut apparaître plusieurs fois : on garde la plus peuplée.
            if (!isset($places[$name]) || $places[$name]['population'] < $population) {
                $places[$name] = ['population' => $population, 'names' => $names];
            }
        }

        return $places;
    }

    /**
     * @param array<string, list<array<string, string>>> $data
     */
    private function writeFile(string $filePath, array $data): void
    {
        file_put_contents(
            $filePath,
            json_encode($data, \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_INVALID_UTF8_SUBSTITUTE | \JSON_THROW_ON_ERROR)."\n",
            \LOCK_EX,
        );
    }
}
