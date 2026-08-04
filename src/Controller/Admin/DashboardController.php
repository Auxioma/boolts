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
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
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

    /**
     * @return iterable<MenuItem>
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

        yield MenuItem::linkTo(TranslationCrudController::class, 'Traductions', 'fas fa-language')
            ->setAction(Action::INDEX);
    }
}