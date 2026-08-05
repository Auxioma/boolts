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

use App\Entity\PropertyImage;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * @extends AbstractCrudController<PropertyImage>
 */
class PropertyImageCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return PropertyImage::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('image de bien')
            ->setEntityLabelInPlural('images des biens')
            ->setPageTitle(Crud::PAGE_INDEX, 'Images des biens')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une image')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier l’image')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de l’image')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            AssociationField::new('property', 'Bien immobilier'),
            ImageField::new('imageName', 'Aperçu')
                ->setBasePath('/properties')
                ->hideOnForm(),
            Field::new('imageFile', 'Fichier image')
                ->setFormType(VichImageType::class)
                ->onlyOnForms(),
            TextField::new('position', 'Position'),
            TextField::new('imageName', 'Nom du fichier')->onlyOnDetail(),
            TextField::new('imageSize', 'Taille (octets)')->onlyOnDetail(),
            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
        ];
    }
}
