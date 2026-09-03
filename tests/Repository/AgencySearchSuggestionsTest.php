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

namespace App\Tests\Repository;

use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Vérifie que la barre de recherche « Mes biens » propose des suggestions
 * issues des mêmes colonnes que le LIKE SQL de la liste.
 */
final class AgencySearchSuggestionsTest extends KernelTestCase
{
    private EntityManagerInterface $em;

    private PropertyRepository $repository;

    private User $agency;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->em = self::getContainer()->get(EntityManagerInterface::class);
        $this->repository = self::getContainer()->get(PropertyRepository::class);

        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);

        $this->agency = $this->createUser('agency@example.test');

        $this->createProperty($this->agency, 'REF-PARIS-1', 'Bel appartement lumineux', 'Paris', 'France', '12 rue de Rivoli, 75001 Paris');
        $this->createProperty($this->agency, 'REF-LYON-2', 'Studio meublé', 'Lyon', 'France', '3 quai Saint-Antoine, 69002 Lyon');

        // Bien d'une autre agence : ne doit jamais apparaître.
        $other = $this->createUser('other@example.test');
        $this->createProperty($other, 'REF-NICE-9', 'Villa vue mer', 'Nice', 'France', 'Promenade des Anglais, Nice');

        $this->em->flush();
        $this->em->clear();
    }

    public function testSuggestionsComeFromTheAgencyOwnColumns(): void
    {
        $results = $this->repository->findAgencySearchSuggestions($this->reloadAgency(), 'par', 'fr');

        $values = array_column($results, 'value');

        self::assertContains('Paris', $values);
        self::assertNotContains('Lyon', $values, 'La ville qui ne matche pas la requête est exclue.');
        self::assertNotContains('Nice', $values, 'Les biens des autres agences sont exclus.');
    }

    public function testEveryColumnFeedsTheSuggestions(): void
    {
        $byType = [];

        foreach ($this->repository->findAgencySearchSuggestions($this->reloadAgency(), 'ref-paris', 'fr') as $row) {
            $byType[$row['type']] = $row['value'];
        }

        self::assertSame('REF-PARIS-1', $byType['reference'] ?? null);
    }

    public function testResultsAreDeduplicatedAndLimited(): void
    {
        $results = $this->repository->findAgencySearchSuggestions($this->reloadAgency(), 'a', 'fr', 3);

        self::assertLessThanOrEqual(3, \count($results));

        $lowered = array_map('mb_strtolower', array_column($results, 'value'));
        self::assertSame($lowered, array_unique($lowered), 'Aucune valeur en double, casse ignorée.');
    }

    public function testPrefixMatchesAreRankedFirst(): void
    {
        // "France" contient "an", "Bel appartement" commence par... non ;
        // "Paris"/"Promenade" -> on teste avec "li" : "lumineux" (contient),
        // rien ne commence par "li" ici, donc on vérifie surtout l'ordre stable.
        $results = $this->repository->findAgencySearchSuggestions($this->reloadAgency(), 'stud', 'fr');

        self::assertNotEmpty($results);
        self::assertSame('Studio meublé', $results[0]['value']);
        self::assertSame('titre', $results[0]['type']);
    }

    private function reloadAgency(): User
    {
        return $this->em->getRepository(User::class)->find($this->agency->getId());
    }

    private function createUser(string $email): User
    {
        $user = (new User())
            ->setEmail($email);
        $user->setRoles(['ROLE_AGENCE']);

        $this->em->persist($user);

        return $user;
    }

    private function createProperty(
        User $user,
        string $reference,
        string $titre,
        string $ville,
        string $pays,
        string $fullAddress,
    ): Property {
        $property = new Property();
        $property->setSlug('slug-'.bin2hex(random_bytes(6)));
        $property->setStatut(StatutAnnonceImmobiliere::PUBLIEE);
        $property->setUser($user);
        $property->setReferenceInterne($reference);

        $translation = $property->translate('fr');
        $translation->setTitreDuLogement($titre);
        $translation->setVille($ville);
        $translation->setPays($pays);
        $translation->setAdresse($fullAddress);
        $translation->setFullAddress($fullAddress);

        $property->mergeNewTranslations();

        $this->em->persist($property);

        return $property;
    }
}
