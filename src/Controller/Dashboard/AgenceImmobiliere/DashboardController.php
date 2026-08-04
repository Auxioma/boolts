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

namespace App\Controller\Dashboard\AgenceImmobiliere;

use App\Entity\Document\RequiredDocument;
use App\Entity\Document\UserDocumentRequest;
use App\Entity\Document\UserDocumentSubmission;
use App\Entity\Enum\DocumentRequestStatus;
use App\Entity\Property;
use App\Entity\User;
use App\Form\Documents\AskDocumentsType;
use App\Repository\AgencyProfileDailyVisitRepository;
use App\Repository\Document\RequiredDocumentRepository;
use App\Repository\Document\UserDocumentRequestRepository;
use App\Repository\FavorisRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyViewRepository;
use Knp\Component\Pager\PaginatorInterface;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response; 
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Doctrine\ORM\EntityManagerInterface;

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
    ): Response
    {
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

        $form = $this->createForm(AskDocumentsType::class, $user);

        $form->handleRequest($request);
        $documentsStepActive = $request->query->getBoolean('documents');

        $documentForms = [];
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

            $documentForms[$requiredDocument->getId()] = $this->createForm(AskDocumentsType::class, $user, [
                'include_country' => false,
                'required_document' => $requiredDocument,
                'csrf_token_id' => 'agency_document_upload',
            ])->createView();
        }

        $requiredDocumentCount = count(array_filter(
            $requiredDocuments,
            static fn (RequiredDocument $requiredDocument): bool => $requiredDocument->isRequired(),
        ));
        $documentsComplete = $requiredDocumentCount > 0
            && $requiredDocumentCount === $userDocumentRequestRepository->countSubmittedRequiredDocuments($user);

        return $this->render('dashboard/agence_immobiliere/dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'statistics' => $statistics,
            'statistics_chart' => $this->buildChart($chartBuilder, $statistics),
            'performance_query_parameters' => $performanceQueryParameters,
            'performance_sort' => $performanceSort,
            'performance_direction' => $performanceDirection,
            'form' => $form->createView(),
            'document_forms' => $documentForms,
            'required_documents' => $requiredDocuments,
            'documents_complete' => $documentsComplete,
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
            'view_counts' => $propertyViewRepository->countByPropertyIds($propertyIds, $start, $end),
            'favorite_counts' => $favorisRepository->countByPropertyIds($propertyIds, $start, $end),
            'boosted_property_ids' => $propertyRepository->findBoostedPropertyIds($propertyIds),
        ]);
    }

    #[Route('/documents/upload', name: 'dashboard_document_upload', methods: ['POST'])]
    public function uploadDocument(
        Request $request,
        EntityManagerInterface $entityManager,
        RequiredDocumentRepository $requiredDocumentRepository,
        UserDocumentRequestRepository $userDocumentRequestRepository,
        Filesystem $filesystem,
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
        $fileName = bin2hex(random_bytes(16)).(is_string($extension) ? '.'.$extension : '');
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

        $requiredDocumentCount = $requiredDocumentRepository->count([
            'enabled' => true,
            'required' => true,
        ]);
        $documentsComplete = $requiredDocumentCount > 0
            && $requiredDocumentCount === $userDocumentRequestRepository->countSubmittedRequiredDocuments($user);

        return $this->documentUploadResponse(
            $request,
            true,
            'Votre document a été téléversé et sera vérifié.',
            Response::HTTP_OK,
            $documentsComplete,
        );
    }

    private function documentUploadResponse(
        Request $request,
        bool $success,
        string $message,
        int $status,
        bool $documentsComplete = false,
        bool $submissionLimitReached = false,
    ): Response {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => $success,
                'message' => $message,
                'documentsComplete' => $documentsComplete,
                'submissionLimitReached' => $submissionLimitReached,
            ], $status);
        }

        $this->addFlash($success ? 'success' : 'error', $message);

        return $this->redirectToRoute('agence_immobiliere_dashboard', ['documents' => 1]);
    }

    private function isSubmissionLimitReached(UserDocumentRequest $documentRequest): bool
    {
        return $documentRequest->getSubmissionCount()
            >= ($documentRequest->getRequiredDocument()?->getMaxSubmissions() ?? 0);
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
                'published' => count($publishedDates),
                'views' => count($viewedDates),
                'favorites' => count($favoriteDates),
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
