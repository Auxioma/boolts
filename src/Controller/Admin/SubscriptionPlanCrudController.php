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

use App\Entity\Billing\SubscriptionPlan;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<SubscriptionPlan>
 */
class SubscriptionPlanCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubscriptionPlan::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('forfait d’abonnement')
            ->setEntityLabelInPlural('forfaits d’abonnement')
            ->setPageTitle(Crud::PAGE_INDEX, 'Forfaits d’abonnement')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un forfait')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le forfait')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du forfait')
            ->setDefaultSort(['position' => 'ASC', 'name' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('code', 'Code')->setColumns(4),
            TextField::new('name', 'Nom')->setColumns(8),
            TextareaField::new('description', 'Description')->setColumns(12),
            IntegerField::new('propertyLimit', 'Limite de biens')->setColumns(4),
            IntegerField::new('includedBoosts', 'Boosts inclus')->setColumns(4),
            IntegerField::new('boostDurationDays', 'Durée d’un boost (jours)')->setColumns(4),
            BooleanField::new('isFree', 'Gratuit')->setColumns(4),
            BooleanField::new('isDefault', 'Forfait par défaut')->setColumns(4),
            BooleanField::new('isActive', 'Actif')->setColumns(4),
            IntegerField::new('position', 'Position')->setColumns(4),
            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
        ];
    }
}
