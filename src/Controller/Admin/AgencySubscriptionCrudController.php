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

use App\Entity\Billing\AgencySubscription;
use App\Entity\Billing\Enum\SubscriptionStatus;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
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
 * @extends AbstractCrudController<AgencySubscription>
 */
class AgencySubscriptionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AgencySubscription::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('forfait acheté')
            ->setEntityLabelInPlural('forfaits achetés par les agences')
            ->setPageTitle(Crud::PAGE_INDEX, 'Forfaits achetés par les agences')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du forfait acheté')
            ->setDefaultSort(['startedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('agency', 'Agence')
                ->formatValue(static fn (?User $agency): string => self::agencyLabel($agency)),
            AssociationField::new('plan', 'Forfait'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'Gratuit' => SubscriptionStatus::FREE,
                'Incomplet' => SubscriptionStatus::INCOMPLETE,
                'Actif' => SubscriptionStatus::ACTIVE,
                'Impayé' => SubscriptionStatus::UNPAID,
                'En retard' => SubscriptionStatus::PAST_DUE,
                'Annulé' => SubscriptionStatus::CANCELED,
                'Expiré' => SubscriptionStatus::EXPIRED,
            ]),
            DateTimeField::new('startedAt', 'Début de l’abonnement'),
            DateTimeField::new('currentPeriodStart', 'Début de période'),
            DateTimeField::new('currentPeriodEnd', 'Fin de période'),
            BooleanField::new('cancelAtPeriodEnd', 'Annulation en fin de période'),
            DateTimeField::new('canceledAt', 'Annulé le'),
            DateTimeField::new('endedAt', 'Terminé le'),
            IntegerField::new('propertyLimitSnapshot', 'Limite de biens enregistrée')->hideOnIndex(),
            IntegerField::new('includedBoostsSnapshot', 'Boosts inclus enregistrés')->hideOnIndex(),
            IntegerField::new('boostDurationDaysSnapshot', 'Durée de boost enregistrée (jours)')->hideOnIndex(),
            IntegerField::new('amountSnapshotMinor', 'Montant enregistré (unité mineure)')->hideOnIndex(),
            AssociationField::new('currencySnapshot', 'Devise enregistrée')->hideOnIndex(),
            TextField::new('providerCustomerId', 'ID client prestataire')->hideOnIndex(),
            TextField::new('providerSubscriptionId', 'ID abonnement prestataire')->hideOnIndex(),
            TextField::new('providerSubscriptionItemId', 'ID ligne d’abonnement prestataire')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Créé le'),
            DateTimeField::new('updatedAt', 'Mis à jour le'),
        ];
    }

    private static function agencyLabel(?User $agency): string
    {
        if (!$agency instanceof User) {
            return '';
        }

        return $agency->getEntreprise() ?? $agency->getEmail() ?? '';
    }
}
