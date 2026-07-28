<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Document\RequiredDocument;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class RequiredDocumentFixtures extends Fixture
{
    public const REQUIRED_DOCUMENT_REFERENCE_PREFIX = 'required_document_';

    /**
     * @var list<array{
     *     reference: string,
     *     name: string,
     *     description: string,
     *     required: bool,
     *     maxSubmissions: int,
     *     position: int,
     *     acceptedMimeTypes: string,
     *     maxFileSizeMb: int
     * }>
     */
    private const DOCUMENTS = [
        [
            'reference' => 'identity-card',
            'name' => 'Pièce d’identité',
            'description' => 'Carte nationale d’identité, passeport ou titre de séjour en cours de validité.',
            'required' => true,
            'maxSubmissions' => 3,
            'position' => 1,
            'acceptedMimeTypes' => 'application/pdf,image/jpeg,image/png',
            'maxFileSizeMb' => 10,
        ],
        [
            'reference' => 'proof-of-address',
            'name' => 'Justificatif de domicile',
            'description' => 'Document de moins de trois mois indiquant votre nom et votre adresse.',
            'required' => true,
            'maxSubmissions' => 3,
            'position' => 2,
            'acceptedMimeTypes' => 'application/pdf,image/jpeg,image/png',
            'maxFileSizeMb' => 10,
        ],
        [
            'reference' => 'company-registration',
            'name' => 'Extrait Kbis',
            'description' => 'Extrait Kbis de moins de trois mois pour les sociétés.',
            'required' => true,
            'maxSubmissions' => 3,
            'position' => 3,
            'acceptedMimeTypes' => 'application/pdf',
            'maxFileSizeMb' => 10,
        ],
        [
            'reference' => 'professional-insurance',
            'name' => 'Attestation d’assurance responsabilité civile professionnelle',
            'description' => 'Attestation d’assurance responsabilité civile professionnelle en cours de validité.',
            'required' => true,
            'maxSubmissions' => 3,
            'position' => 4,
            'acceptedMimeTypes' => 'application/pdf,image/jpeg,image/png',
            'maxFileSizeMb' => 10,
        ],
        [
            'reference' => 'professional-card',
            'name' => 'Carte professionnelle immobilière',
            'description' => 'Carte professionnelle immobilière ou attestation d’habilitation en cours de validité.',
            'required' => true,
            'maxSubmissions' => 3,
            'position' => 5,
            'acceptedMimeTypes' => 'application/pdf,image/jpeg,image/png',
            'maxFileSizeMb' => 10,
        ],
        [
            'reference' => 'bank-account-details',
            'name' => 'Relevé d’identité bancaire',
            'description' => 'RIB du compte destiné aux versements.',
            'required' => false,
            'maxSubmissions' => 3,
            'position' => 6,
            'acceptedMimeTypes' => 'application/pdf,image/jpeg,image/png',
            'maxFileSizeMb' => 10,
        ],
    ];

    public function load(ObjectManager $manager): void
    {
        foreach (self::DOCUMENTS as $data) {
            $document = new RequiredDocument();
            $document
                ->setName($data['name'])
                ->setDescription($data['description'])
                ->setRequired($data['required'])
                ->setEnabled(true)
                ->setMaxSubmissions($data['maxSubmissions'])
                ->setPosition($data['position'])
                ->setAcceptedMimeTypes($data['acceptedMimeTypes'])
                ->setMaxFileSizeMb($data['maxFileSizeMb']);

            $manager->persist($document);
            $this->addReference(
                self::REQUIRED_DOCUMENT_REFERENCE_PREFIX.$data['reference'],
                $document,
            );
        }

        $manager->flush();
    }
}
