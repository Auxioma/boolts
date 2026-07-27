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

use App\Entity\User;
use App\Repository\FavorisRepository;
use App\Repository\PropertyRepository;
use App\Repository\PropertyViewRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
    ): Response
    {
        $statistics = $this->statistics(
            $request,
            $propertyRepository,
            $propertyViewRepository,
            $favorisRepository,
        );

        return $this->render('dashboard/agence_immobiliere/dashboard/index.html.twig', [
            'controller_name' => 'DashboardController',
            'statistics' => $statistics,
            'statistics_chart' => $this->buildChart($chartBuilder, $statistics),
        ]);
    }

    #[Route('/statistics', name: 'dashboard_statistics', methods: ['GET'])]
    public function statisticsData(
        Request $request,
        ChartBuilderInterface $chartBuilder,
        PropertyRepository $propertyRepository,
        PropertyViewRepository $propertyViewRepository,
        FavorisRepository $favorisRepository,
    ): JsonResponse {
        try {
            $statistics = $this->statistics(
                $request,
                $propertyRepository,
                $propertyViewRepository,
                $favorisRepository,
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
    ): array {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw $this->createAccessDeniedException();
        }

        [$period, $start, $end] = $this->resolvePeriod($request);

        $publishedDates = $propertyRepository->findPublishedDatesForDashboard($user, $start, $end);
        $viewedDates = $propertyViewRepository->findViewedDatesForDashboard($user, $start, $end);
        $favoriteDates = $favorisRepository->findCreatedDatesForPropertyOwnerDashboard($user, $start, $end);

        $chartStart = $start ?? $this->firstDate($publishedDates, $viewedDates, $favoriteDates) ?? $end;
        $monthly = $chartStart->diff($end)->days > 31;
        $buckets = $this->buckets($chartStart, $end, $monthly);

        return [
            'period' => $period,
            'start' => $chartStart,
            'end' => $end,
            'totals' => [
                'published' => count($publishedDates),
                'views' => count($viewedDates),
                'favorites' => count($favoriteDates),
            ],
            'series' => [
                'labels' => array_values(array_map(static fn (array $bucket): string => $bucket['label'], $buckets)),
                'published' => $this->countByBucket($publishedDates, $buckets, $monthly),
                'views' => $this->countByBucket($viewedDates, $buckets, $monthly),
                'favorites' => $this->countByBucket($favoriteDates, $buckets, $monthly),
            ],
        ];
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
}
