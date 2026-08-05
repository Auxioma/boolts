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

use App\Entity\Billing\Enum\SubscriptionBillingPeriod;
use App\Entity\Billing\SubscriptionPlanPrice;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<SubscriptionPlanPrice>
 */
class SubscriptionPlanPriceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return SubscriptionPlanPrice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('tarif de forfait')
            ->setEntityLabelInPlural('tarifs des forfaits')
            ->setPageTitle(Crud::PAGE_INDEX, 'Tarifs des forfaits')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un tarif de forfait')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le tarif du forfait')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du tarif du forfait')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            AssociationField::new('plan', 'Forfait')->setColumns(6),
            AssociationField::new('currency', 'Devise')->setColumns(6),
            IntegerField::new('amountMinor', 'Montant (unité mineure)')->setColumns(4),
            ChoiceField::new('billingPeriod', 'Période de facturation')
                ->setChoices([
                    'Mensuelle' => SubscriptionBillingPeriod::MONTHLY,
                    'Annuelle' => SubscriptionBillingPeriod::ANNUAL,
                ])
                ->setColumns(4),
            BooleanField::new('isActive', 'Actif')->setColumns(4),
            TextField::new('paymentProviderPriceId', 'ID tarif prestataire')->setColumns(12),
            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
        ];
    }
}
