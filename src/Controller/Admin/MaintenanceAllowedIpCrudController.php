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

use App\Entity\MaintenanceAllowedIp;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MaintenanceAllowedIpCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MaintenanceAllowedIp::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Adresse IP autorisée')
            ->setEntityLabelInPlural('Adresses IP autorisées')
            ->setPageTitle(
                Crud::PAGE_INDEX,
                'Adresses IP autorisées pendant la maintenance'
            )
            ->setPageTitle(
                Crud::PAGE_NEW,
                'Ajouter une adresse IP'
            )
            ->setPageTitle(
                Crud::PAGE_EDIT,
                'Modifier une adresse IP'
            )
            ->setDefaultSort([
                'enabled' => 'DESC',
                'createdAt' => 'DESC',
            ]);
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/maintenance-ip-field.js');
    }

    public function configureFields(string $pageName): iterable
    {
        $currentIp = $this->getContext()?->getRequest()->getClientIp();

        yield IdField::new('id')
            ->hideOnForm();

        yield TextField::new(
            'label',
            'Nom / description'
        )
            ->setHelp(
                'Exemple : Guillaume bureau, Développeur, Agence, VPN, etc.'
            );

        yield TextField::new(
            'ipAddress',
            'Adresse IP'
        )
            ->setFormTypeOption('attr', [
                'data-current-ip' => $currentIp ?? '',
            ])
            ->setHelp(
                'IPv4 ou IPv6. Exemple : 82.64.100.25 ou 2a01:e0a:1234::1'
            );

        yield BooleanField::new(
            'enabled',
            'Autorisé'
        );

        yield DateTimeField::new(
            'createdAt',
            'Ajoutée le'
        )
            ->hideOnForm();

        yield DateTimeField::new(
            'updatedAt',
            'Modifiée le'
        )
            ->hideOnForm();
    }
}
