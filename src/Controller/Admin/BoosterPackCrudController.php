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

use App\Entity\Booster\BoosterPack;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<BoosterPack>
 */
class BoosterPackCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return BoosterPack::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('pack de boosts')
            ->setEntityLabelInPlural('packs de boosts')
            ->setPageTitle(Crud::PAGE_INDEX, 'Packs de boosts')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un pack de boosts')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le pack de boosts')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du pack de boosts')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('code', 'Code')->setColumns(4),
            TextField::new('name', 'Nom')->onlyOnIndex(),
            TextField::new('nameFr', 'Nom (FR)')->hideOnIndex()->setColumns(4),
            TextField::new('nameEn', 'Nom (EN)')->hideOnIndex()->setColumns(4),
            TextareaField::new('descriptionFr', 'Description (FR)')->hideOnIndex()->setColumns(6)->setRequired(false),
            TextareaField::new('descriptionEn', 'Description (EN)')->hideOnIndex()->setColumns(6)->setRequired(false),
            IntegerField::new('boostQuantity', 'Nombre de boosts')->setColumns(4),
            IntegerField::new('boostDurationDays', 'Durée d’un boost (jours)')->setColumns(4),
            BooleanField::new('isActive', 'Actif')->setColumns(4),
            IntegerField::new('position', 'Position')->setColumns(4),
            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
        ];
    }

    // Les traductions FR/EN créées depuis le formulaire doivent être
    // rattachées au pack avant l'enregistrement (KnpDoctrineBehaviors).

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof BoosterPack) {
            $entityInstance->mergeNewTranslations();
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof BoosterPack) {
            $entityInstance->mergeNewTranslations();
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
