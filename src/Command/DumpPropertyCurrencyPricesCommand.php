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

use App\Entity\Devise;
use App\Entity\Property;
use App\Entity\PropertyPrixDevise;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Intl\Currencies;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Convertit les montants de chaque bien dans toutes les devises de la table `devise`
 * et les enregistre dans `property_prix_devise`.
 *
 * Les montants saisis par l'agence (table `property`) ne sont jamais modifiés : ils servent
 * de référence, dans la devise du bien (Property::devise, sinon devise du profil de l'agence).
 */
#[AsCommand(
    name: 'app:property:dump-currency-prices',
    description: 'Convertit les prix des biens dans toutes les devises et les enregistre dans property_prix_devise.'
)]
class DumpPropertyCurrencyPricesCommand extends Command
{
    private const API_URL = 'https://api.frankfurter.dev/v2/rates';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $devisesByCode = $this->loadDevisesByCode();

            if ([] === $devisesByCode) {
                $output->writeln('<error>Aucune devise avec un code ISO trouvée.</error>');

                return Command::FAILURE;
            }

            /** @var Property[] $properties */
            $properties = $this->entityManager->getRepository(Property::class)->findAll();

            /** @var array<string, array{rates: array<string, float>, dates: array<string, \DateTimeImmutable>}> $ratesByBase */
            $ratesByBase = [];
            $missingRates = [];
            $converted = 0;
            $skipped = 0;
            $rows = 0;

            foreach ($properties as $property) {
                $baseDevise = $property->getDevise() ?? $property->getUser()?->getDevise();
                $baseCode = $baseDevise instanceof Devise ? $this->resolveCode($baseDevise) : null;

                if (null === $baseCode || !$this->hasAmount($property)) {
                    ++$skipped;
                    continue;
                }

                if (!isset($ratesByBase[$baseCode])) {
                    $ratesByBase[$baseCode] = $this->fetchRates($baseCode);
                    $output->writeln(\sprintf(
                        '<comment>%d taux récupérés pour la base %s.</comment>',
                        \count($ratesByBase[$baseCode]['rates']),
                        $baseCode
                    ));
                }

                ['rates' => $rates, 'dates' => $dates] = $ratesByBase[$baseCode];

                foreach ($devisesByCode as $code => $devise) {
                    $rate = $code === $baseCode ? 1.0 : ($rates[$code] ?? null);

                    if (null === $rate) {
                        $missingRates[$baseCode.' → '.$code] = true;
                        continue;
                    }

                    $prixDevise = $property->getPrixDevise($devise);

                    if (!$prixDevise instanceof PropertyPrixDevise) {
                        $prixDevise = (new PropertyPrixDevise())->setDevise($devise);
                        $property->addPrixDevise($prixDevise);
                    }

                    $prixDevise
                        ->setPrix($this->convert($property->getPrix(), $rate, $code))
                        ->setMontantLoyerHorsCharge($this->convert($property->getMontantLoyerHorsCharge(), $rate, $code))
                        ->setMontantDepotDeGarantie($this->convert($property->getMontantDepotDeGarantie(), $rate, $code))
                        ->setMontantDesCharges($this->convert($property->getMontantDesCharges(), $rate, $code))
                        ->setTauxDeChange(number_format($rate, 10, '.', ''))
                        ->setConvertedAt($dates[$code] ?? new \DateTimeImmutable('today'));

                    ++$rows;
                }

                ++$converted;
                $this->entityManager->flush();
            }

            $output->writeln('');
            $output->writeln(\sprintf(
                '<info>%d biens convertis (%d lignes enregistrées dans property_prix_devise), %d biens ignorés (sans montant ou sans devise).</info>',
                $converted,
                $rows,
                $skipped
            ));

            if ([] !== $missingRates) {
                $output->writeln('<comment>Taux indisponibles dans l’API : '.implode(', ', array_keys($missingRates)).'</comment>');
            }

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>Erreur : '.$exception->getMessage().'</error>');

            return Command::FAILURE;
        }
    }

    /**
     * @return array<string, Devise>
     */
    private function loadDevisesByCode(): array
    {
        $devisesByCode = [];

        foreach ($this->entityManager->getRepository(Devise::class)->findAll() as $devise) {
            $code = $this->resolveCode($devise);

            if (null !== $code) {
                $devisesByCode[$code] = $devise;
            }
        }

        return $devisesByCode;
    }

    /**
     * Code ISO de la devise : colonne `code`, sinon repris du libellé "euro (EUR)".
     */
    private function resolveCode(Devise $devise): ?string
    {
        if (null !== $devise->getCode() && '' !== $devise->getCode()) {
            return $devise->getCode();
        }

        if (1 === preg_match('/\(([A-Z]{3})\)\s*$/', (string) $devise->getNom(), $matches)) {
            return $matches[1];
        }

        return null;
    }

    private function hasAmount(Property $property): bool
    {
        foreach ([
            $property->getPrix(),
            $property->getMontantLoyerHorsCharge(),
            $property->getMontantDepotDeGarantie(),
            $property->getMontantDesCharges(),
        ] as $amount) {
            if (null !== $this->toNumber($amount)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{rates: array<string, float>, dates: array<string, \DateTimeImmutable>}
     */
    private function fetchRates(string $baseCode): array
    {
        $response = $this->httpClient->request('GET', self::API_URL, [
            'query' => ['base' => $baseCode],
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode < 200 || $statusCode >= 300) {
            throw new \RuntimeException(\sprintf('API Frankfurter (base %s) : HTTP %d — %s', $baseCode, $statusCode, $content));
        }

        $data = json_decode($content, true);

        if (!\is_array($data)) {
            throw new \RuntimeException(\sprintf('API Frankfurter (base %s) : réponse JSON invalide.', $baseCode));
        }

        /*
         * Format Frankfurter v2 : [{"date": "2026-11-10", "base": "EUR", "quote": "USD", "rate": 1.16}, …]
         */
        $rates = [];
        $dates = [];

        foreach ($data as $item) {
            if (!\is_array($item) || !isset($item['quote'], $item['rate'])) {
                continue;
            }

            $quote = mb_strtoupper((string) $item['quote']);
            $rates[$quote] = (float) $item['rate'];

            if (isset($item['date'])) {
                $dates[$quote] = new \DateTimeImmutable((string) $item['date']);
            }
        }

        return ['rates' => $rates, 'dates' => $dates];
    }

    private function convert(?string $amount, float $rate, string $code): ?string
    {
        $number = $this->toNumber($amount);

        if (null === $number) {
            return null;
        }

        // Arrondi selon la devise (JPY : 0 décimale, EUR : 2…), colonne limitée à 2 décimales.
        $decimals = min(2, Currencies::getFractionDigits($code));

        return number_format(round($number * $rate, $decimals), 2, '.', '');
    }

    /**
     * Dépôt de garantie et charges sont encore en VARCHAR : on n'accepte qu'une valeur numérique.
     */
    private function toNumber(?string $amount): ?float
    {
        if (null === $amount) {
            return null;
        }

        $normalized = str_replace([' ', "\u{00A0}", "\u{202F}", ','], ['', '', '', '.'], $amount);

        return is_numeric($normalized) ? (float) $normalized : null;
    }
}
