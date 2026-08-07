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

use App\Entity\MaintenanceSetting;
use App\Repository\MaintenanceSettingRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MaintenanceSettingCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly MaintenanceSettingRepository $maintenanceSettingRepository,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return MaintenanceSetting::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Maintenance')
            ->setEntityLabelInPlural('Maintenance du site')
            ->setPageTitle(Crud::PAGE_INDEX, 'Maintenance BOOLTS')
            ->setPageTitle(Crud::PAGE_EDIT, 'Configurer la maintenance')
            ->setDefaultSort([
                'id' => 'ASC',
            ]);
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions->disable(Action::DELETE);

        if ($this->maintenanceSettingRepository->getSettings() !== null) {
            $actions->disable(Action::NEW);
        }

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IdField::new('id')
            ->hideOnForm();

        yield BooleanField::new(
            'enabled',
            'Maintenance active'
        )
            ->setHelp(
                'Lorsque cette option est activée, seuls les visiteurs dont l’adresse IP est autorisée peuvent accéder au site.'
            );

        yield TextField::new(
            'title',
            'Titre'
        );

        yield TextareaField::new(
            'message',
            'Message'
        )
            ->setNumOfRows(8)
            ->setHelp(
                'Message affiché publiquement pendant la maintenance.'
            );

        yield DateTimeField::new(
            'startsAt',
            'Début automatique'
        )
            ->setHelp(
                'Facultatif. Si vide, la maintenance commence immédiatement dès son activation.'
            );

        yield DateTimeField::new(
            'endsAt',
            'Fin automatique'
        )
            ->setHelp(
                'Facultatif. À cette date, le site redevient automatiquement accessible.'
            );

        yield DateTimeField::new(
            'updatedAt',
            'Dernière modification'
        )
            ->hideOnForm();
    }
}