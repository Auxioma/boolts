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

use App\Entity\CategoryBienTransaction;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\String\Slugger\SluggerInterface;

class CategoryBienTransactionFixtures extends Fixture
{
    public const CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX = 'category_bien_transaction_';

    public function __construct(
        private readonly SluggerInterface $slugger,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $transactions = [
            [
                'icone' => 'icon-pencil-line',
                'translations' => [
                    'fr' => 'Vente',
                    'en' => 'Sale',
                ],
            ],
            [
                'icone' => 'icon-key-round',
                'translations' => [
                    'fr' => 'Location',
                    'en' => 'Rent',
                ],
            ],
        ];

        foreach ($transactions as $transactionData) {
            $transaction = new CategoryBienTransaction();
            $transaction->setIcone($transactionData['icone']);

            foreach ($transactionData['translations'] as $locale => $name) {
                $slug = mb_strtolower(
                    $this->slugger->slug($name)->toString()
                );

                $translation = $transaction->translate($locale);
                $translation->setName($name);
                $translation->setSlug($slug);
            }

            $transaction->mergeNewTranslations();

            $manager->persist($transaction);

            $referenceSlug = mb_strtolower(
                $this->slugger->slug($transactionData['translations']['fr'])->toString()
            );

            $this->addReference(
                self::CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX.$referenceSlug,
                $transaction
            );
        }

        $manager->flush();
    }
}
