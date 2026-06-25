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

namespace App\Command;

use App\Entity\Property;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[AsCommand(
    name: 'app:property:dump-currency-prices',
    description: 'Affiche les prix convertis des biens immobiliers sans modifier la base de données.'
)]
class DumpPropertyCurrencyPricesCommand extends Command
{
    private const BASE_CURRENCY = 'EUR';

    private const TARGET_CURRENCIES = [
        'CAD',
        'GBP',
        'JPY',
        'USD',
    ];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HttpClientInterface $httpClient,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $rates = $this->fetchRates($output);

            if ([] === $rates) {
                $output->writeln('<error>Aucun taux de change récupéré.</error>');

                return Command::FAILURE;
            }

            $properties = $this->entityManager
                ->getRepository(Property::class)
                ->findAll();

            $output->writeln('');
            $output->writeln('<info>Taux de change récupérés depuis Frankfurter :</info>');

            foreach ($rates as $currency => $rate) {
                $output->writeln(\sprintf(
                    '<comment>1 %s = %s %s</comment>',
                    self::BASE_CURRENCY,
                    $rate,
                    $currency
                ));
            }

            $output->writeln('');

            $table = new Table($output);
            $table->setHeaders([
                'ID',
                'Prix original',
                'EUR',
                'CAD',
                'GBP',
                'JPY',
                'USD',
            ]);

            $count = 0;

            foreach ($properties as $property) {
                if (null === $property->getPrix()) {
                    continue;
                }

                $price = (float) $property->getPrix();

                if ($price <= 0) {
                    continue;
                }

                $table->addRow([
                    $property->getId(),
                    $this->formatPrice($price, self::BASE_CURRENCY),
                    $this->formatPrice($price, 'EUR'),
                    $this->convert($price, $rates, 'CAD'),
                    $this->convert($price, $rates, 'GBP'),
                    $this->convert($price, $rates, 'JPY'),
                    $this->convert($price, $rates, 'USD'),
                ]);

                ++$count;
            }

            if (0 === $count) {
                $output->writeln('<comment>Aucun bien avec un prix trouvé.</comment>');

                return Command::SUCCESS;
            }

            $table->render();

            $output->writeln('');
            $output->writeln(\sprintf(
                '<info>%d prix affichés dans la console. Aucune donnée n’a été enregistrée en base.</info>',
                $count
            ));

            return Command::SUCCESS;
        } catch (\Throwable $exception) {
            $output->writeln('<error>Erreur : '.$exception->getMessage().'</error>');

            return Command::FAILURE;
        }
    }

    private function fetchRates(OutputInterface $output): array
    {
        $response = $this->httpClient->request('GET', 'https://api.frankfurter.dev/v2/rates', [
            'query' => [
                'base' => self::BASE_CURRENCY,
                'quotes' => implode(',', self::TARGET_CURRENCIES),
            ],
        ]);

        $statusCode = $response->getStatusCode();
        $content = $response->getContent(false);

        if ($statusCode < 200 || $statusCode >= 300) {
            $output->writeln('<error>Erreur API Frankfurter.</error>');
            $output->writeln('<comment>Status : '.$statusCode.'</comment>');
            $output->writeln('<comment>Réponse : '.$content.'</comment>');

            return [];
        }

        $data = json_decode($content, true);

        if (!\is_array($data)) {
            $output->writeln('<error>Réponse JSON invalide.</error>');
            $output->writeln('<comment>Réponse : '.$content.'</comment>');

            return [];
        }

        return $this->normalizeRates($data);
    }

    private function normalizeRates(array $data): array
    {
        $rates = [];

        /*
         * Format Frankfurter v2 reçu :
         *
         * [
         *     [
         *         "date" => "2026-06-24",
         *         "base" => "EUR",
         *         "quote" => "CAD",
         *         "rate" => 1.6176,
         *     ],
         * ]
         */
        foreach ($data as $item) {
            if (
                !\is_array($item)
                || !isset($item['quote'], $item['rate'])
            ) {
                continue;
            }

            $quote = mb_strtoupper((string) $item['quote']);

            if (!\in_array($quote, self::TARGET_CURRENCIES, true)) {
                continue;
            }

            $rates[$quote] = (float) $item['rate'];
        }

        return $rates;
    }

    private function convert(float $price, array $rates, string $currency): string
    {
        if (!isset($rates[$currency])) {
            return '-';
        }

        $convertedPrice = $price * (float) $rates[$currency];

        return $this->formatPrice($convertedPrice, $currency);
    }

    private function formatPrice(float $price, string $currency): string
    {
        $decimals = 'JPY' === $currency ? 0 : 2;

        return number_format(
            round($price, $decimals),
            $decimals,
            '.',
            ' '
        ).' '.$currency;
    }
}
