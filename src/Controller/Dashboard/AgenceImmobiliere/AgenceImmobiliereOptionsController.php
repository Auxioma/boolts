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

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\SubscriptionPlanPrice;
use App\Entity\Booster\BoosterPackPrice;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/immobiliere/options', name: 'agence_immobiliere_')]
#[IsGranted('ROLE_AGENCE')]
final class AgenceImmobiliereOptionsController extends AbstractController
{
    public function __construct(
       
    ){}

    #[Route('/', name: 'options')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $agency = $this->getUser();

        if (!$agency instanceof User) {
            throw $this->createAccessDeniedException('Utilisateur non authentifié.');
        }

        $subscriptionPrices = $entityManager
            ->getRepository(SubscriptionPlanPrice::class)
            ->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('plan.position', 'ASC')
            ->addOrderBy('price.billingPeriod', 'ASC')
            ->getQuery()
            ->getResult();

        $forfaits = [];

        foreach ($subscriptionPrices as $subscriptionPrice) {
            $plan = $subscriptionPrice->getPlan();
            $planId = $plan->getId();

            if (!isset($forfaits[$planId])) {
                $forfaits[$planId] = [
                    'plan' => $plan,
                    'monthly' => null,
                    'annual' => null,
                ];
            }

            $forfaits[$planId][$subscriptionPrice->getBillingPeriod()->value] = $subscriptionPrice;
        }

        $boosterPackPrices = $entityManager
            ->getRepository(BoosterPackPrice::class)
            ->createQueryBuilder('price')
            ->addSelect('pack', 'currency')
            ->innerJoin('price.boosterPack', 'pack')
            ->innerJoin('price.currency', 'currency')
            ->where('price.isActive = :active')
            ->andWhere('pack.isActive = :active')
            ->setParameter('active', true)
            ->orderBy('pack.position', 'ASC')
            ->getQuery()
            ->getResult();

        $currentSubscription = $entityManager
            ->getRepository(AgencySubscription::class)
            ->createQueryBuilder('subscription')
            ->addSelect('plan', 'price')
            ->innerJoin('subscription.plan', 'plan')
            ->leftJoin('subscription.planPrice', 'price')
            ->where('subscription.agency = :agency')
            ->setParameter('agency', $agency)
            ->orderBy('subscription.startedAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_options/index.html.twig',
            [
                'forfaits' => $forfaits,
                'packs_boost' => $boosterPackPrices,
                'abonnement_actuel' => $currentSubscription,
            ]
        );
    }

    #[Route('/achat/{id}', name: 'achat')]
    public function achat(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $period = $request->query->get('period', 'monthly');

        if (!in_array($period, ['monthly', 'annual'], true)) {
            throw $this->createNotFoundException('Période de facturation invalide.');
        }

        $planPrice = $entityManager
            ->getRepository(SubscriptionPlanPrice::class)
            ->createQueryBuilder('price')
            ->addSelect('plan', 'currency')
            ->innerJoin('price.plan', 'plan')
            ->innerJoin('price.currency', 'currency')
            ->where('plan.id = :id')
            ->andWhere('price.billingPeriod = :period')
            ->andWhere('price.isActive = :active')
            ->andWhere('plan.isActive = :active')
            ->setParameter('id', $id)
            ->setParameter('period', SubscriptionBillingPeriod::from($period))
            ->setParameter('active', true)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$planPrice instanceof SubscriptionPlanPrice) {
            throw $this->createNotFoundException('Forfait indisponible pour cette période de facturation.');
        }

        return $this->render(
            'dashboard/agence_immobiliere/agence_immobiliere_options/achat.html.twig',
            [
                'plan' => $planPrice->getPlan(),
                'plan_price' => $planPrice,
                'period' => $period,
            ]
        );
    }
}
