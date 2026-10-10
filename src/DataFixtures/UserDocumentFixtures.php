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

use App\Entity\Document\RequiredDocument;
use App\Entity\Document\UserDocumentRequest;
use App\Entity\Document\UserDocumentSubmission;
use App\Entity\Enum\DocumentRequestStatus;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Filesystem\Filesystem;

/**
 * Documents des agences déjà déposés et validés par l'administrateur :
 * chaque document requis a une demande APPROVED et une soumission approuvée.
 */
final class UserDocumentFixtures extends Fixture implements DependentFixtureInterface
{
    /**
     * PDF d'une page, utilisé comme fichier de démonstration pour chaque soumission.
     */
    private const PLACEHOLDER_PDF = "%PDF-1.4\n1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 595 842]>>endobj\ntrailer<</Root 1 0 R>>\n%%EOF\n";

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        private readonly string $projectDir,
        private readonly Filesystem $filesystem,
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        $administrator = $this->getReference(UserFixtures::USER_ADMIN_REFERENCE, User::class);
        $requiredDocuments = $manager->getRepository(RequiredDocument::class)->findBy(['enabled' => true], ['position' => 'ASC']);

        for ($i = 1; $i <= UserFixtures::AGENCY_COUNT; ++$i) {
            $agency = $this->getReference(UserFixtures::USER_AGENCE_REFERENCE_PREFIX.$i, User::class);

            foreach ($requiredDocuments as $requiredDocument) {
                $documentRequest = FixtureEntityHelper::findOrCreate($manager, UserDocumentRequest::class, [
                    'user' => $agency,
                    'requiredDocument' => $requiredDocument,
                ]);
                $documentRequest
                    ->setUser($agency)
                    ->setRequiredDocument($requiredDocument);

                if ($documentRequest->getSubmissions()->isEmpty()) {
                    $documentRequest->addSubmission($this->createSubmission($agency, $requiredDocument));
                }

                foreach ($documentRequest->getSubmissions() as $submission) {
                    $submission->approve($administrator);
                    $manager->persist($submission);
                }

                if (DocumentRequestStatus::APPROVED !== $documentRequest->getStatus()) {
                    $documentRequest->markAsCompleted();
                }

                $manager->persist($documentRequest);
            }

            // Évite d'envoyer à l'agence le mail « Votre compte a été validé » au premier passage en admin.
            $agency->setDocumentReviewOutcomeNotified(DocumentRequestStatus::APPROVED->value);
        }

        $manager->flush();
    }

    private function createSubmission(User $agency, RequiredDocument $requiredDocument): UserDocumentSubmission
    {
        $fileName = bin2hex(random_bytes(16)).'.pdf';
        $storagePath = 'uploads/document/'.$agency->getId().'/'.$fileName;
        $absolutePath = $this->projectDir.'/public/'.$storagePath;

        $this->filesystem->dumpFile($absolutePath, self::PLACEHOLDER_PDF);

        return (new UserDocumentSubmission())
            ->setFileName($fileName)
            ->setOriginalFileName($requiredDocument->getName().'.pdf')
            ->setMimeType('application/pdf')
            ->setFileSize(\strlen(self::PLACEHOLDER_PDF))
            ->setStoragePath($storagePath)
            ->setChecksum(hash('sha256', self::PLACEHOLDER_PDF))
            ->setAttemptNumber(1);
    }

    public function getDependencies(): array
    {
        return [
            UserFixtures::class,
            RequiredDocumentFixtures::class,
        ];
    }
}
