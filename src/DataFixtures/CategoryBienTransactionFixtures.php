<?php

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
            ['name' => 'Vente', 'icone' => 'icon-pencil-line'],
            ['name' => 'Location', 'icone' => 'icon-key-round'],
        ];

        foreach ($transactions as $transactionData) {
            $transaction = new CategoryBienTransaction();

            $slug = strtolower(
                $this->slugger->slug($transactionData['name'])->toString()
            );

            $transaction
                ->setName($transactionData['name'])
                ->setIcone($transactionData['icone'])
                ->setSlug($slug);

            $manager->persist($transaction);

            $this->addReference(
                self::CATEGORY_BIEN_TRANSACTION_REFERENCE_PREFIX . $slug,
                $transaction
            );
        }

        $manager->flush();
    }
}
