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

use App\Entity\Document\RequiredDocument;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * @extends AbstractCrudController<RequiredDocument>
 */
final class RequiredDocumentCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return RequiredDocument::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('type de document')
            ->setEntityLabelInPlural('types de documents')
            ->setPageTitle(Crud::PAGE_INDEX, 'Types de documents demandés')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter un type de document')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le type de document')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du type de document')
            ->setDefaultSort(['position' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            TextField::new('name', 'Nom')->setColumns(8),
            IntegerField::new('position', 'Position')->setColumns(4),
            TextareaField::new('description', 'Description')->setColumns(12),
            BooleanField::new('required', 'Obligatoire')->setColumns(6),
            BooleanField::new('enabled', 'Actif')->setColumns(6),
            TextField::new('acceptedMimeTypes', 'Types MIME acceptés')
                ->setHelp('Séparez les types MIME par des virgules, par exemple application/pdf,image/jpeg.')
                ->setColumns(12),
            IntegerField::new('maxFileSizeMb', 'Taille maximale (Mo)')->setColumns(6),
            IntegerField::new('maxSubmissions', 'Nombre maximal d’envois')->setColumns(6),
            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
        ];
    }
}
