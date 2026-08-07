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

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Menu\MenuItemInterface;
use Symfony\Component\HttpFoundation\Response;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        return $this->render('bundles/EasyAdminBundle/Page/home_page.html.twig');
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Boolts');
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addJsFile('js/property-price-fields.js');
    }

    /**
     * @return iterable<MenuItemInterface>
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Utilisateurs & Accès', 'fas fa-users');

        yield MenuItem::linkTo(UserCrudController::class, 'Visiteurs', 'fas fa-user')
            ->setAction(Action::INDEX)
            ->setQueryParameter('role', 'ROLE_USER');

        yield MenuItem::linkTo(UserCrudController::class, 'Agences', 'fas fa-building')
            ->setAction(Action::INDEX)
            ->setQueryParameter('role', 'ROLE_AGENCE');

        yield MenuItem::section('Biens immobiliers', 'fas fa-house');

        yield MenuItem::linkTo(PropertyCrudController::class, 'Biens immobiliers', 'fas fa-building')
            ->setAction(Action::INDEX);

        yield MenuItem::linkTo(PropertyImageCrudController::class, 'Images des biens', 'fas fa-images')
            ->setAction(Action::INDEX);

        yield MenuItem::section('Forfaits et tarifs', 'fas fa-tags');

        yield MenuItem::linkTo(SubscriptionPlanCrudController::class, 'Forfaits d’abonnement', 'fas fa-box')
            ->setAction(Action::INDEX);

        yield MenuItem::linkTo(SubscriptionPlanPriceCrudController::class, 'Tarifs des forfaits', 'fas fa-money-bill')
            ->setAction(Action::INDEX);

        yield MenuItem::linkTo(BoosterPackCrudController::class, 'Packs de boosts', 'fas fa-rocket')
            ->setAction(Action::INDEX);

        yield MenuItem::linkTo(BoosterPackPriceCrudController::class, 'Tarifs des boosts', 'fas fa-coins')
            ->setAction(Action::INDEX);

        yield MenuItem::section('Paiements', 'fas fa-receipt');

        yield MenuItem::linkTo(AgencySubscriptionCrudController::class, 'Forfaits achetés ', 'fas fa-receipt')
            ->setAction(Action::INDEX);

        yield MenuItem::linkTo(BoosterPurchaseCrudController::class, 'Boosts achetés', 'fas fa-rocket')
            ->setAction(Action::INDEX);


        yield MenuItem::section('Traductions', 'fas fa-language');

        yield MenuItem::linkTo(TranslationCrudController::class, 'Traductions', 'fas fa-language')
            ->setAction(Action::INDEX);

        yield MenuItem::section('Système', 'fas fa-gears');

yield MenuItem::linkTo(
    MaintenanceSettingCrudController::class,
    'Maintenance',
    'fas fa-wrench'
)
    ->setAction(Action::INDEX);

yield MenuItem::linkTo(
    MaintenanceAllowedIpCrudController::class,
    'IP autorisées',
    'fas fa-network-wired'
)
    ->setAction(Action::INDEX);
    
    }
}