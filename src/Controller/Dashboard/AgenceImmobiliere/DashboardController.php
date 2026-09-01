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

namespace App\Controller\Dashboard\AgenceImmobiliere;

use App\Entity\Document\RequiredDocument;
use App\Entity\Document\UserDocumentRequest;
use App\Entity\Document\UserDocumentSubmission;
use App\Entity\Enum\DocumentRequestStatus;
use App\Entity\Enum\DocumentSubmissionStatus;
use App\Entity\Pays;
use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\User;
use App\Form\Documents\AskDocumentsType;
use App\Repository\AgencyNotificationRepository;
use App\Repository\AgencyProfileDailyVisitRepository;
use App\Repository\Booster\BoosterTransactionRepository;
use App\Repository\Document\RequiredDocumentRepository;
use App\Repository\Document\UserDocumentRequestRepository;
use App\Repository\Document\UserDocumentSubmissionRepository;
use App\Repository\FavorisRepository;
use App\Repository\PaysRepository;
use App\Repository\PropertyImageRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyViewRepository;
use App\Security\Voter\AgencyDocumentVoter;
use App\Service\Document\AdminDocumentNotificationMailer;
use App\Service\GeoIpLocationService;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

#[Route('/pro/dashboard', name: 'agence_immobiliere_')]
/**
 * HTTP controller for module Dashboard / AgenceImmobiliere / DashboardController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class DashboardController extends AbstractController
{
    private const PERFORMANCE_PROPERTIES_PER_PAGE = 10;

    #[Route('/', name: 'dashboard')]
    /**
     * Handles the index controller action.
     */
    public function index(
        Request $request,
        ChartBuilderInterface $chartBuilder,
        PropertyRepository $propertyRepository,
        PropertyViewRepository $propertyViewRepository,
        FavorisRepository $favorisRepository,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
        RequiredDocumentRepository $requiredDocumentRepository,
        UserDocumentRequestRepository $userDocumentRequestRepository,
        UserDocumentSubmissionRepository $userDocumentSubmissionRepository,
        GeoIpLocationService $geoIpLocationService,
        PaysRepository $paysRepository,
        AgencyNotificationRepository $agencyNotificationRepository,
    ): Response {
        $statistics = $this->statistics(
            $request,
            $propertyRepository,
            $propertyViewRepository,
            $favorisRepository,
            $agencyProfileDailyVisitRepository,
        );
        [$performanceSort, $performanceDirection] = $this->resolvePerformanceSort($request);
        $performanceQueryParameters = [
            'period' => $statistics['period'],
            'performance_sort' => $performanceSort,
            'performance_direction' => mb_strtolower($performanceDirection),
        ];

        if ('custom' === $statistics['period']) {
            $performanceQueryParameters['start'] = $statistics['start']->format('Y-m-d');
            $performanceQueryParameters['end'] = $statistics['end']->format('Y-m-d');
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $ipCountry = $this->detectCountryFromIp($request, $geoIpLocationService, $paysRepository);

        if ($ipCountry instanceof Pays) {
            $user->setPays($ipCountry);
        }

        $form = $this->createForm(AskDocumentsType::class, $user);

        $form->handleRequest($request);

        $documentForms = [];
        $submittedDocumentNames = [];
        $submittedDocumentStatuses = [];
        $documentsSubmissionLimitReached = false;
        $requiredDocuments = $requiredDocumentRepository->findBy(
            ['enabled' => true],
            ['position' => 'ASC', 'name' => 'ASC'],
        );

        foreach ($requiredDocuments as $requiredDocument) {
            $documentRequest = $userDocumentRequestRepository->findForUserAndRequiredDocument($user, $requiredDocument);
            $documentsSubmissionLimitReached = $documentsSubmissionLimitReached
                || ($documentRequest instanceof UserDocumentRequest
                    && $this->isSubmissionLimitReached($documentRequest));

            $latestSubmission = $documentRequest instanceof UserDocumentRequest
                ? $documentRequest->getLatestSubmission()
                : null;

            if ($latestSubmission instanceof UserDocumentSubmission && null !== $requiredDocument->getId()) {
                $submittedDocumentNames[$requiredDocument->getId()] = $latestSubmission->getOriginalFileName();
                $submittedDocumentStatuses[$requiredDocument->getId()] = $latestSubmission->getStatus()->value;
            }

            $documentForms[$requiredDocument->getId()] = $this->createForm(AskDocumentsType::class, $user, [
                'include_country' => false,
                'required_document' => $requiredDocument,
                'csrf_token_id' => 'agency_document_upload',
            ])->createView();
        }

        $documentsComplete = $this->areRequiredDocumentsApproved($user, $userDocumentSubmissionRepository);
        $requiredDocumentCount = \count(array_filter(
            $requiredDocuments,
            static fn (RequiredDocument $requiredDocument): bool => $requiredDocument->isRequired(),
        ));
        $documentsSubmitted = $requiredDocumentCount > 0
            && $requiredDocumentCount === $userDocumentRequestRepository->countSubmittedRequiredDocuments($user);
        $hasRejectedDocument = \in_array(DocumentSubmissionStatus::REJECTED->value, $submittedDocumentStatuses, true);
        $documentsUnderReview = $documentsSubmitted && !$documentsComplete && !$hasRejectedDocument;
        $documentsStepActive = $request->query->getBoolean('documents')
            || (!$documentsComplete && ([] !== $submittedDocumentNames || $documentsSubmitted));

        return $this->render('dashboard/agence_immobiliere/dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'notifications' => $agencyNotificationRepository->findLatestForAgency($user, 2),
            'statistics' => $statistics,
            'statistics_chart' => $this->buildChart($chartBuilder, $statistics),
            'performance_query_parameters' => $performanceQueryParameters,
            'performance_sort' => $performanceSort,
            'performance_direction' => $performanceDirection,
            'form' => $form->createView(),
            'document_forms' => $documentForms,
            'required_documents' => $requiredDocuments,
            'submitted_document_names' => $submittedDocumentNames,
            'submitted_document_statuses' => $submittedDocumentStatuses,
            'documents_complete' => $documentsComplete,
            'documents_under_review' => $documentsUnderReview,
            'documents_submission_limit_reached' => $documentsSubmissionLimitReached,
            'documents_step_active' => $documentsStepActive,
        ]);
    }

    #[Route('/performances', name: 'dashboard_performances', methods: ['GET'])]
    public function performances(
        Request $request,
        PropertyRepository $propertyRepository,
        PropertyViewRepository $propertyViewRepository,
        FavorisRepository $favorisRepository,
        BoosterTransactionRepository $boosterTransactionRepository,
        PaginatorInterface $paginator,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        try {
            [, $start, $end] = $this->resolvePeriod($request);
        } catch (\InvalidArgumentException $exception) {
            return new Response($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        [$sort, $direction] = $this->resolvePerformanceSort($request);
        $affichePerformanceAnnonce = $this->paginatePerformanceProperties(
            $user,
            $request,
            $propertyRepository,
            $paginator,
            $start,
            $end,
            $sort,
            $direction,
        );
        $propertyIds = [];

        foreach ($affichePerformanceAnnonce->getItems() as $property) {
            if ($property instanceof Property && null !== $property->getId()) {
                $propertyIds[] = $property->getId();
            }
        }

        return $this->render('dashboard/agence_immobiliere/dashboard/_property_performance.html.twig', [
            'affiche_performance_annonce' => $affichePerformanceAnnonce,
            'view_counts' => $propertyViewRepository->countByPropertyIds($propertyIds),
            'favorite_counts' => $favorisRepository->countByPropertyIds($propertyIds),
            'boosted_property_ids' => $propertyRepository->findBoostedPropertyIds($propertyIds),
            'boosts_restants' => $boosterTransactionRepository->countAvailableForAgency($user),
        ]);
    }

    #[Route('/properties/export', name: 'dashboard_properties_export', methods: ['GET'])]
    public function exportProperties(
        Request $request,
        PropertyRepository $propertyRepository,
        PropertyViewRepository $propertyViewRepository,
        FavorisRepository $favorisRepository,
        PropertyImageRepository $propertyImageRepository,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $properties = $propertyRepository->findForDashboardExport($user);
        $propertyIds = array_values(array_filter(
            array_map(static fn (Property $property): ?int => $property->getId(), $properties),
            static fn (?int $propertyId): bool => null !== $propertyId,
        ));

        $viewCounts = $propertyViewRepository->countByPropertyIds($propertyIds);
        $favoriteCounts = $favorisRepository->countByPropertyIds($propertyIds);
        $imageCounts = $propertyImageRepository->countByPropertyIds($propertyIds);
        $boostedPropertyIds = $propertyRepository->findBoostedPropertyIds($propertyIds);
        $boostedPropertyIds = array_flip($boostedPropertyIds);
        $filename = \sprintf('biens-immobiliers-%s.csv', (new \DateTimeImmutable())->format('Ymd-His'));
        $baseUrl = $request->getSchemeAndHttpHost().$request->getBaseUrl();

        $response = new StreamedResponse(function () use (
            $properties,
            $viewCounts,
            $favoriteCounts,
            $imageCounts,
            $boostedPropertyIds,
            $baseUrl,
        ): void {
            $output = fopen('php://output', 'w');

            if (false === $output) {
                throw new \RuntimeException('Impossible d’ouvrir le flux de sortie CSV.');
            }

            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $this->propertyExportHeaders(), ';');

            foreach ($properties as $property) {
                fputcsv(
                    $output,
                    $this->propertyExportRow(
                        $property,
                        $viewCounts[$property->getId()] ?? 0,
                        $favoriteCounts[$property->getId()] ?? 0,
                        $imageCounts[$property->getId()] ?? 0,
                        isset($boostedPropertyIds[$property->getId()]),
                        $baseUrl,
                    ),
                    ';',
                );
            }

            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', \sprintf('attachment; filename="%s"', $filename));

        return $response;
    }

    #[Route('/documents/upload', name: 'dashboard_document_upload', methods: ['POST'])]
    public function uploadDocument(
        Request $request,
        EntityManagerInterface $entityManager,
        UserDocumentRequestRepository $userDocumentRequestRepository,
        UserDocumentSubmissionRepository $userDocumentSubmissionRepository,
        Filesystem $filesystem,
        AdminDocumentNotificationMailer $adminDocumentNotificationMailer,
    ): Response {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $requiredDocumentId = $request->request->all('ask_documents')['requiredDocument'] ?? null;
        $requiredDocument = null;

        if (is_numeric($requiredDocumentId)) {
            $requiredDocument = $entityManager->find(RequiredDocument::class, (int) $requiredDocumentId);
        }

        if (!$requiredDocument instanceof RequiredDocument || !$requiredDocument->isEnabled()) {
            return $this->documentUploadResponse($request, false, 'Le document demandé est introuvable.', Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(AskDocumentsType::class, $user, [
            'include_country' => false,
            'required_document' => $requiredDocument,
            'csrf_token_id' => 'agency_document_upload',
        ]);
        $form->handleRequest($request);

        if (!$form->isSubmitted() || !$form->isValid()) {
            $errors = [];

            foreach ($form->getErrors(true) as $error) {
                $errors[] = $error->getMessage();
            }

            return $this->documentUploadResponse(
                $request,
                false,
                [] === $errors ? 'Le fichier transmis est invalide.' : implode(' ', $errors),
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        $file = $form->get('document')->getData();

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->documentUploadResponse($request, false, 'Veuillez sélectionner un fichier valide.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $mimeType = $file->getMimeType() ?? $file->getClientMimeType() ?? 'application/octet-stream';
        $fileSize = $file->getSize();

        if ('application/pdf' !== $mimeType && !str_starts_with($mimeType, 'image/')) {
            return $this->documentUploadResponse($request, false, 'Le format du document n’est pas pris en charge', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        if ($fileSize > $requiredDocument->getMaxFileSizeMb() * 1024 * 1024) {
            return $this->documentUploadResponse($request, false, 'Le document est trop volumineux', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $documentRequest = $userDocumentRequestRepository->findForUserAndRequiredDocument($user, $requiredDocument);

        if (!$documentRequest instanceof UserDocumentRequest) {
            $documentRequest = (new UserDocumentRequest())
                ->setUser($user)
                ->setRequiredDocument($requiredDocument);
        }

        if (!$documentRequest->canSubmit()) {
            return $this->documentUploadResponse(
                $request,
                false,
                'Vous ne pouvez plus envoyer ce document.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
                false,
                $this->isSubmissionLimitReached($documentRequest),
            );
        }

        $directory = $this->getParameter('kernel.project_dir').'/public/uploads/document/'.$user->getId();
        $filesystem->mkdir($directory);
        $extension = $file->guessExtension();
        $fileName = bin2hex(random_bytes(16)).(\is_string($extension) ? '.'.$extension : '');
        $file->move($directory, $fileName);
        $storagePath = 'uploads/document/'.$user->getId().'/'.$fileName;

        $submission = (new UserDocumentSubmission())
            ->setDocumentRequest($documentRequest)
            ->setFileName($fileName)
            ->setOriginalFileName($file->getClientOriginalName())
            ->setMimeType($mimeType)
            ->setFileSize($fileSize)
            ->setStoragePath($storagePath)
            ->setChecksum(hash_file('sha256', $directory.'/'.$fileName))
            ->setAttemptNumber($documentRequest->getSubmissionCount() + 1);

        $documentRequest
            ->addSubmission($submission)
            ->setStatus(DocumentRequestStatus::UNDER_REVIEW);

        $entityManager->persist($documentRequest);
        $entityManager->persist($submission);
        $entityManager->flush();

        if ($request->request->getBoolean('notifyAdmin')) {
            $adminNotificationRequiredDocumentIds = $this->adminNotificationRequiredDocumentIds($request);

            if ([] === $adminNotificationRequiredDocumentIds && null !== $requiredDocument->getId()) {
                $adminNotificationRequiredDocumentIds = [$requiredDocument->getId()];
            }

            $adminNotificationSubmissions = $userDocumentSubmissionRepository->findLatestForUserAndRequiredDocuments(
                $user,
                $adminNotificationRequiredDocumentIds,
                [DocumentSubmissionStatus::PENDING],
            );

            $adminDocumentNotificationMailer->sendPendingDocumentNotification($user, $adminNotificationSubmissions);
        }

        $documentsComplete = $this->areRequiredDocumentsApproved($user, $userDocumentSubmissionRepository);

        return $this->documentUploadResponse(
            $request,
            true,
            'Votre document a été envoyé et sera vérifié.',
            Response::HTTP_OK,
            $documentsComplete,
            false,
            $submission->getOriginalFileName(),
        );
    }

    private function documentUploadResponse(
        Request $request,
        bool $success,
        string $message,
        int $status,
        bool $documentsComplete = false,
        bool $submissionLimitReached = false,
        ?string $originalFileName = null,
    ): Response {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => $success,
                'message' => $message,
                'documentsComplete' => $documentsComplete,
                'submissionLimitReached' => $submissionLimitReached,
                'originalFileName' => $originalFileName,
            ], $status);
        }

        $this->addFlash($success ? 'success' : 'error', $message);

        return $this->redirectToRoute('agence_immobiliere_dashboard', ['documents' => 1]);
    }

    private function areRequiredDocumentsApproved(
        User $user,
        UserDocumentSubmissionRepository $userDocumentSubmissionRepository,
    ): bool {
        return $userDocumentSubmissionRepository->hasLatestSubmissionForEveryRequiredDocumentWithStatus(
            $user,
            [DocumentSubmissionStatus::APPROVED],
        );
    }

    private function isSubmissionLimitReached(UserDocumentRequest $documentRequest): bool
    {
        return $documentRequest->getSubmissionCount()
            >= ($documentRequest->getRequiredDocument()?->getMaxSubmissions() ?? 0);
    }

    /**
     * @return list<int>
     */
    private function adminNotificationRequiredDocumentIds(Request $request): array
    {
        $requiredDocumentIds = $request->request->all('adminNotificationRequiredDocumentIds');

        return array_values(array_unique(array_filter(
            array_map(
                static fn (mixed $requiredDocumentId): int => is_numeric($requiredDocumentId)
                    ? (int) $requiredDocumentId
                    : 0,
                $requiredDocumentIds,
            ),
            static fn (int $requiredDocumentId): bool => $requiredDocumentId > 0,
        )));
    }

    private function detectCountryFromIp(
        Request $request,
        GeoIpLocationService $geoIpLocationService,
        PaysRepository $paysRepository,
    ): ?Pays {
        $location = $geoIpLocationService->locateIp($request->getClientIp());
        $countryCode = $location['countryCode'] ?? null;

        if (!\is_string($countryCode) || '' === mb_trim($countryCode)) {
            return null;
        }

        return $paysRepository->findOneBy([
            'iso' => mb_strtoupper(mb_trim($countryCode)),
        ]);
    }

    private function propertyExportHeaders(): array
    {
        return [
            'id',
            'slug',
            'statut',
            'booste',
            'type_bien_fr',
            'type_bien_en',
            'type_transaction_fr',
            'type_transaction_en',
            'reference_interne',
            'prix',
            'montant_loyer_hors_charge',
            'montant_depot_de_garantie',
            'montant_des_charges',
            'devise_nom',
            'devise_signe',
            'code_postal',
            'latitude',
            'longitude',
            'mapbox_id',
            'session_id_mapbox',
            'feature_type',
            'show_adresse',
            'adresse_fr',
            'ville_fr',
            'pays_fr',
            'adresse_complete_fr',
            'region_fr',
            'district_fr',
            'localite_fr',
            'quartier_fr',
            'point_interet_fr',
            'titre_fr',
            'description_fr',
            'adresse_en',
            'ville_en',
            'pays_en',
            'adresse_complete_en',
            'region_en',
            'district_en',
            'localite_en',
            'quartier_en',
            'point_interet_en',
            'titre_en',
            'description_en',
            'annee_construction',
            'chambres',
            'salle_de_bains',
            'surface_total',
            'dpe',
            'dpe_lettre',
            'ges',
            'ges_lettre',
            'dpe_min',
            'dpe_max',
            'date_indexation_energie',
            'caracteristiques_fr',
            'caracteristiques_en',
            'nombre_images',
            'urls_images',
            'nombre_vues',
            'nombre_favoris',
            'agence_id',
            'agence_email',
            'agence_nom',
            'created_at',
            'updated_at',
        ];
    }

    private function propertyExportRow(
        Property $property,
        int $viewCount,
        int $favoriteCount,
        int $imageCount,
        bool $boosted,
        string $baseUrl,
    ): array {
        $agency = $property->getUser();
        $currency = $agency?->getDevise();
        $agencyName = mb_trim(\sprintf('%s %s', $agency?->getPrenom() ?? '', $agency?->getNom() ?? ''));

        return [
            $this->csvValue($property->getId()),
            $this->csvValue($property->getSlug()),
            $this->csvValue($property->getStatut()->value),
            $this->csvBoolean($boosted),
            $this->translatedValue($property->getTypeBien(), 'fr', 'getName'),
            $this->translatedValue($property->getTypeBien(), 'en', 'getName'),
            $this->translatedValue($property->getTypeTransaction(), 'fr', 'getName'),
            $this->translatedValue($property->getTypeTransaction(), 'en', 'getName'),
            $this->csvValue($property->getReferenceInterne()),
            $this->csvValue($property->getPrix()),
            $this->csvValue($property->getMontantLoyerHorsCharge()),
            $this->csvValue($property->getMontantDepotDeGarantie()),
            $this->csvValue($property->getMontantDesCharges()),
            $this->csvValue($currency?->getNom()),
            $this->csvValue($currency?->getSigne()),
            $this->csvValue($property->getCodePostal()),
            $this->csvValue($property->getLatitude()),
            $this->csvValue($property->getLongitude()),
            $this->csvValue($property->getMapboxId()),
            $this->csvValue($property->getSessionIdMapbox()),
            $this->csvValue($property->getFeatureType()),
            $this->csvBoolean($property->isShowAdresse()),
            $this->translatedValue($property, 'fr', 'getAdresse'),
            $this->translatedValue($property, 'fr', 'getVille'),
            $this->translatedValue($property, 'fr', 'getPays'),
            $this->translatedValue($property, 'fr', 'getFullAddress'),
            $this->translatedValue($property, 'fr', 'getRegion'),
            $this->translatedValue($property, 'fr', 'getDistrict'),
            $this->translatedValue($property, 'fr', 'getLocality'),
            $this->translatedValue($property, 'fr', 'getNeighborhood'),
            $this->translatedValue($property, 'fr', 'getPoi'),
            $this->translatedValue($property, 'fr', 'getTitreDuLogement'),
            $this->translatedValue($property, 'fr', 'getDescriptionLogement'),
            $this->translatedValue($property, 'en', 'getAdresse'),
            $this->translatedValue($property, 'en', 'getVille'),
            $this->translatedValue($property, 'en', 'getPays'),
            $this->translatedValue($property, 'en', 'getFullAddress'),
            $this->translatedValue($property, 'en', 'getRegion'),
            $this->translatedValue($property, 'en', 'getDistrict'),
            $this->translatedValue($property, 'en', 'getLocality'),
            $this->translatedValue($property, 'en', 'getNeighborhood'),
            $this->translatedValue($property, 'en', 'getPoi'),
            $this->translatedValue($property, 'en', 'getTitreDuLogement'),
            $this->translatedValue($property, 'en', 'getDescriptionLogement'),
            $this->csvValue($property->getAnneeConstruction()),
            $this->csvValue($property->getChambres()),
            $this->csvValue($property->getSalleDeBains()),
            $this->csvValue($property->getSurfaceTotal()),
            $this->csvValue($property->getDpe()),
            $this->csvValue($property->getDpeLettre()),
            $this->csvValue($property->getGes()),
            $this->csvValue($property->getGesLettre()),
            $this->csvValue($property->getDpeMin()),
            $this->csvValue($property->getDpeMax()),
            $this->csvDate($property->getDateIndexationEnergie(), 'Y-m-d'),
            $this->translatedValues($property->getCaracteristique(), 'fr', 'getNom'),
            $this->translatedValues($property->getCaracteristique(), 'en', 'getNom'),
            $this->csvValue($imageCount),
            $this->propertyImageUrls($property, $baseUrl),
            $this->csvValue($viewCount),
            $this->csvValue($favoriteCount),
            $this->csvValue($agency?->getId()),
            $this->csvValue($agency?->getEmail()),
            $this->csvValue($agencyName),
            $this->csvDate($property->getCreatedAt()),
            $this->csvDate($property->getUpdatedAt()),
        ];
    }

    private function propertyImageUrls(Property $property, string $baseUrl): string
    {
        $urls = [];

        foreach ($property->getPropertyImages() as $image) {
            if (!$image instanceof PropertyImage || null === $image->getImageName()) {
                continue;
            }

            $path = $image->getLiipPath() ?? $image->getImageName();
            $path = str_replace('\\', '/', mb_ltrim($path, '/'));
            $path = preg_replace('#^(public/)?properties/#', '', $path) ?? $path;

            if ('' === $path) {
                continue;
            }

            $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));
            $urls[] = mb_rtrim($baseUrl, '/').'/properties/'.$encodedPath;
        }

        return implode(' | ', array_values(array_unique($urls)));
    }

    private function translatedValues(iterable $items, string $locale, string $getter): string
    {
        $values = [];

        foreach ($items as $item) {
            if (!\is_object($item)) {
                continue;
            }

            $value = $this->translatedValue($item, $locale, $getter);

            if ('' !== $value) {
                $values[] = $value;
            }
        }

        return implode(' | ', array_values(array_unique($values)));
    }

    private function translatedValue(?object $translatable, string $locale, string $getter): string
    {
        if (null === $translatable || !method_exists($translatable, 'getTranslations')) {
            return '';
        }

        $translation = $translatable->getTranslations()->get($locale);

        if (null === $translation || !method_exists($translation, $getter)) {
            return '';
        }

        return $this->csvValue($translation->{$getter}());
    }

    private function csvDate(?\DateTimeInterface $date, string $format = 'Y-m-d H:i:s'): string
    {
        return null === $date ? '' : $date->format($format);
    }

    private function csvBoolean(?bool $value): string
    {
        return match ($value) {
            true => 'oui',
            false => 'non',
            null => '',
        };
    }

    private function csvValue(mixed $value): string
    {
        if (null === $value) {
            return '';
        }

        if ($value instanceof \DateTimeInterface) {
            return $this->csvDate($value);
        }

        if ($value instanceof \BackedEnum) {
            return (string) $value->value;
        }

        if (\is_bool($value)) {
            return $this->csvBoolean($value);
        }

        if (\is_scalar($value)) {
            return (string) $value;
        }

        return '';
    }

    #[Route('/statistics', name: 'dashboard_statistics', methods: ['GET'])]
    public function statisticsData(
        Request $request,
        ChartBuilderInterface $chartBuilder,
        PropertyRepository $propertyRepository,
        PropertyViewRepository $propertyViewRepository,
        FavorisRepository $favorisRepository,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
    ): JsonResponse {
        try {
            $statistics = $this->statistics(
                $request,
                $propertyRepository,
                $propertyViewRepository,
                $favorisRepository,
                $agencyProfileDailyVisitRepository,
            );
        } catch (\InvalidArgumentException $exception) {
            return $this->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return $this->json([
            ...$statistics['totals'],
            'chart' => $this->buildChart($chartBuilder, $statistics)->createView(),
        ]);
    }

    private function statistics(
        Request $request,
        PropertyRepository $propertyRepository,
        PropertyViewRepository $propertyViewRepository,
        FavorisRepository $favorisRepository,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
    ): array {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        [$period, $start, $end] = $this->resolvePeriod($request);

        if (!$this->isGranted(AgencyDocumentVoter::ACCESS_RESTRICTED_DASHBOARD)) {
            return $this->previewStatistics($period, $start, $end);
        }

        $publishedDates = $propertyRepository->findPublishedDatesForDashboard($user, $start, $end);
        $viewedDates = $propertyViewRepository->findViewedDatesForDashboard($user, $start, $end);
        $favoriteDates = $favorisRepository->findCreatedDatesForPropertyOwnerDashboard($user, $start, $end);
        $profileDailyViews = $agencyProfileDailyVisitRepository->findForDashboard($user, $start, $end);
        $profileViewDates = array_map(
            static fn ($dailyView): \DateTimeImmutable => $dailyView->getViewedOn() ?? new \DateTimeImmutable('today'),
            $profileDailyViews
        );

        $chartStart = $start ?? $this->firstDate($publishedDates, $viewedDates, $favoriteDates, $profileViewDates) ?? $end;
        $monthly = $chartStart->diff($end)->days > 31;
        $buckets = $this->buckets($chartStart, $end, $monthly);

        return [
            'period' => $period,
            'start' => $chartStart,
            'end' => $end,
            'totals' => [
                'profileViews' => array_sum(array_map(static fn ($dailyView): int => $dailyView->getVisits(), $profileDailyViews)),
                'published' => \count($publishedDates),
                'views' => \count($viewedDates),
                'favorites' => \count($favoriteDates),
            ],
            'series' => [
                'labels' => array_values(array_map(static fn (array $bucket): string => $bucket['label'], $buckets)),
                'profileViews' => $this->sumByBucket($profileDailyViews, $buckets, $monthly),
                'published' => $this->countByBucket($publishedDates, $buckets, $monthly),
                'views' => $this->countByBucket($viewedDates, $buckets, $monthly),
                'favorites' => $this->countByBucket($favoriteDates, $buckets, $monthly),
            ],
        ];
    }

    /**
     * @return array{
     *     period: string,
     *     start: \DateTimeImmutable,
     *     end: \DateTimeImmutable,
     *     totals: array{profileViews: int, published: int, views: int, favorites: int},
     *     series: array{
     *         labels: list<string>,
     *         profileViews: list<int>,
     *         published: list<int>,
     *         views: list<int>,
     *         favorites: list<int>
     *     }
     * }
     */
    private function previewStatistics(
        string $period,
        ?\DateTimeImmutable $start,
        \DateTimeImmutable $end,
    ): array {
        return [
            'period' => $period,
            'start' => $start ?? $end->modify('-11 months')->modify('first day of this month')->setTime(0, 0),
            'end' => $end,
            'totals' => [
                'profileViews' => 284,
                'published' => 18,
                'views' => 736,
                'favorites' => 92,
            ],
            'series' => [
                'labels' => ['01', '05', '09', '13', '17', '21', '25', '29'],
                'profileViews' => [18, 32, 27, 46, 39, 58, 51, 63],
                'published' => [1, 3, 2, 4, 3, 5, 6, 7],
                'views' => [42, 76, 65, 118, 94, 137, 126, 154],
                'favorites' => [4, 9, 7, 16, 12, 21, 18, 25],
            ],
        ];
    }

    /** @return PaginationInterface<int, Property> */
    private function paginatePerformanceProperties(
        User $user,
        Request $request,
        PropertyRepository $propertyRepository,
        PaginatorInterface $paginator,
        ?\DateTimeImmutable $start,
        \DateTimeImmutable $end,
        string $sort,
        string $direction,
    ): PaginationInterface {
        return $paginator->paginate(
            $propertyRepository->findForDashboardPerformanceQuery($user, $start, $end, $sort, $direction),
            $request->query->getInt('performance_page', 1),
            self::PERFORMANCE_PROPERTIES_PER_PAGE,
            [
                'pageParameterName' => 'performance_page',
                'sortFieldParameterName' => 'performance_sort_field',
                'sortDirectionParameterName' => 'performance_sort_direction',
            ],
        );
    }

    private function resolvePerformanceSort(Request $request): array
    {
        $sort = $request->query->getString('performance_sort', 'created');
        $direction = mb_strtoupper($request->query->getString('performance_direction', 'desc'));

        if (!\in_array($sort, ['created', 'views', 'favorites'], true)) {
            $sort = 'created';
        }

        if (!\in_array($direction, ['ASC', 'DESC'], true)) {
            $direction = 'DESC';
        }

        return [$sort, $direction];
    }

    private function resolvePeriod(Request $request): array
    {
        $period = $request->query->getString('period', '30d');
        $end = (new \DateTimeImmutable('today'))->setTime(23, 59, 59);

        return match ($period) {
            '7d' => [$period, $end->modify('-6 days')->setTime(0, 0), $end],
            '30d' => [$period, $end->modify('-29 days')->setTime(0, 0), $end],
            '12m' => [$period, $end->modify('-11 months')->modify('first day of this month')->setTime(0, 0), $end],
            'max' => [$period, null, $end],
            'custom' => $this->customPeriod($request),
            default => throw new \InvalidArgumentException('Période de statistiques invalide.'),
        };
    }

    private function customPeriod(Request $request): array
    {
        $startValue = $request->query->getString('start');
        $endValue = $request->query->getString('end');

        if ('' === $startValue || '' === $endValue) {
            throw new \InvalidArgumentException('Les dates de début et de fin sont obligatoires.');
        }

        try {
            $start = (new \DateTimeImmutable($startValue))->setTime(0, 0);
            $end = (new \DateTimeImmutable($endValue))->setTime(23, 59, 59);
        } catch (\Exception) {
            throw new \InvalidArgumentException('Les dates fournies sont invalides.');
        }

        if ($start > $end || $end > new \DateTimeImmutable('today 23:59:59') || $start->modify('+12 months') < $end) {
            throw new \InvalidArgumentException('La période personnalisée doit être comprise entre aujourd’hui et les 12 derniers mois.');
        }

        return ['custom', $start, $end];
    }

    private function buildChart(ChartBuilderInterface $chartBuilder, array $statistics): Chart
    {
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $statistics['series']['labels'],
            'datasets' => [
                ['label' => 'Vues du profil', 'data' => $statistics['series']['profileViews'], 'borderColor' => '#d27a00', 'backgroundColor' => 'rgba(210, 122, 0, .12)', 'tension' => 0],
                ['label' => 'Annonces publiées', 'data' => $statistics['series']['published'], 'borderColor' => '#1c8c62', 'backgroundColor' => 'rgba(28, 140, 98, .12)', 'tension' => 0],
                ['label' => 'Vues des annonces', 'data' => $statistics['series']['views'], 'borderColor' => '#7746d5', 'backgroundColor' => 'rgba(119, 70, 213, .12)', 'tension' => 0],
                ['label' => 'Mises en favoris', 'data' => $statistics['series']['favorites'], 'borderColor' => '#42ae79', 'backgroundColor' => 'rgba(66, 174, 121, .12)', 'tension' => 0],
            ],
        ]);
        $chart->setOptions([
            'maintainAspectRatio' => false,
            'animation' => [
                'duration' => 700,
                'easing' => 'easeOutQuart',
            ],
            'plugins' => ['legend' => ['display' => true]],
            'scales' => ['y' => ['beginAtZero' => true]],
        ]);

        return $chart;
    }

    private function firstDate(array ...$dateLists): ?\DateTimeImmutable
    {
        $dates = array_merge(...$dateLists);

        if ([] === $dates) {
            return null;
        }

        usort($dates, static fn (\DateTimeImmutable $left, \DateTimeImmutable $right): int => $left <=> $right);

        return $dates[0]->setTime(0, 0);
    }

    private function buckets(\DateTimeImmutable $start, \DateTimeImmutable $end, bool $monthly): array
    {
        $cursor = $monthly ? $start->modify('first day of this month')->setTime(0, 0) : $start;
        $buckets = [];

        while ($cursor <= $end) {
            $key = $monthly ? $cursor->format('Y-m') : $cursor->format('Y-m-d');
            $buckets[$key] = ['label' => $monthly ? $cursor->format('m/Y') : $cursor->format('d/m'), 'count' => 0];
            $cursor = $monthly ? $cursor->modify('+1 month') : $cursor->modify('+1 day');
        }

        return $buckets;
    }

    private function countByBucket(array $dates, array $buckets, bool $monthly): array
    {
        foreach ($dates as $date) {
            $key = $monthly ? $date->format('Y-m') : $date->format('Y-m-d');

            if (isset($buckets[$key])) {
                ++$buckets[$key]['count'];
            }
        }

        return array_values(array_map(static fn (array $bucket): int => $bucket['count'], $buckets));
    }

    private function sumByBucket(array $dailyViews, array $buckets, bool $monthly): array
    {
        foreach ($dailyViews as $dailyView) {
            $viewedOn = $dailyView->getViewedOn();

            if (null === $viewedOn) {
                continue;
            }

            $key = $monthly ? $viewedOn->format('Y-m') : $viewedOn->format('Y-m-d');

            if (isset($buckets[$key])) {
                $buckets[$key]['count'] += $dailyView->getVisits();
            }
        }

        return array_values(array_map(static fn (array $bucket): int => $bucket['count'], $buckets));
    }
}
