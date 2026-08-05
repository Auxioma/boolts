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

use App\Entity\Billing\AgencySubscriptionPeriod;
use App\Entity\Billing\Enum\BoosterTransactionType;
use App\Entity\Billing\Payment;
use App\Entity\Booster\BoosterTransaction;
use App\Entity\Property;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/**
 * @extends AbstractCrudController<BoosterTransaction>
 */
class BoosterPurchaseCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BoosterTransaction::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('boost acheté')
            ->setEntityLabelInPlural('boosts achetés par les agences')
            ->setPageTitle(Crud::PAGE_INDEX, 'Boosts achetés par les agences')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du boost acheté')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::EDIT, Action::DELETE);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        return parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters)
            ->andWhere('entity.type = :type')
            ->setParameter('type', BoosterTransactionType::PACK_PURCHASE->value);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID'),
            AssociationField::new('agency', 'Agence')
                ->formatValue(static fn (?User $agency): string => self::agencyLabel($agency)),
            AssociationField::new('boosterPack', 'Pack de boosts'),
            IntegerField::new('quantity', 'Nombre de boosts'),
            ChoiceField::new('type', 'Type')->setChoices([
                'Achat de pack' => BoosterTransactionType::PACK_PURCHASE,
            ])->hideOnIndex(),
            DateTimeField::new('expiresAt', 'Expire le'),
            TextareaField::new('description', 'Description'),
            AssociationField::new('property', 'Bien immobilier')
                ->formatValue(static fn (?Property $property): string => self::propertyLabel($property))
                ->hideOnIndex(),
            AssociationField::new('subscriptionPeriod', 'Période d’abonnement')
                ->formatValue(static fn (?AgencySubscriptionPeriod $period): string => self::subscriptionPeriodLabel($period))
                ->hideOnIndex(),
            AssociationField::new('payment', 'Paiement')
                ->formatValue(static fn (?Payment $payment): string => $payment?->getReference() ?? '')
                ->hideOnIndex(),
            TextField::new('idempotencyKey', 'Clé d’idempotence')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Créé le'),
            DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnIndex(),
        ];
    }

    private static function agencyLabel(?User $agency): string
    {
        if (!$agency instanceof User) {
            return '';
        }

        return $agency->getEntreprise() ?? $agency->getEmail() ?? '';
    }

    private static function propertyLabel(?Property $property): string
    {
        if (!$property instanceof Property) {
            return '';
        }

        return $property->getReferenceInterne() ?? $property->getSlug() ?? '';
    }

    private static function subscriptionPeriodLabel(?AgencySubscriptionPeriod $period): string
    {
        if (!$period instanceof AgencySubscriptionPeriod) {
            return '';
        }

        return sprintf(
            '#%d - %s au %s',
            $period->getId() ?? 0,
            $period->getPeriodStart()->format('d/m/Y'),
            $period->getPeriodEnd()->format('d/m/Y'),
        );
    }
}
