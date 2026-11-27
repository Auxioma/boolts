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

use App\Entity\HomeVideo;
use App\Service\Intl\VisitorLocaleResolver;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Intl\Languages;
use Vich\UploaderBundle\Form\Type\VichFileType;
use Vich\UploaderBundle\Form\Type\VichImageType;

/**
 * Vidéos de fond de la home : 2 par langue du site, une tirée au hasard sur la home.
 *
 * @extends AbstractCrudController<HomeVideo>
 */
class HomeVideoCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return HomeVideo::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('vidéo de la home')
            ->setEntityLabelInPlural('vidéos de la home')
            ->setPageTitle(Crud::PAGE_INDEX, 'Vidéos de la home (2 par langue)')
            ->setPageTitle(Crud::PAGE_NEW, 'Ajouter une vidéo')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier la vidéo')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de la vidéo')
            ->setHelp(
                Crud::PAGE_INDEX,
                'La home affiche au hasard une des 2 vidéos de la langue de la page. Sans vidéo pour une langue, l’ancienne vidéo aléatoire est utilisée.'
            )
            ->setDefaultSort(['locale' => 'ASC', 'emplacement' => 'ASC']);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id', 'ID')->hideOnForm(),
            ChoiceField::new('locale', 'Langue')
                ->setChoices($this->localeChoices()),
            ChoiceField::new('emplacement', 'Emplacement')
                ->setChoices(['Vidéo 1' => 1, 'Vidéo 2' => 2]),
            ImageField::new('posterName', 'Poster')
                ->setBasePath('/video')
                ->hideOnForm(),
            TextField::new('videoName', 'Fichier vidéo')->hideOnForm(),
            Field::new('videoFile', 'Vidéo (MP4 ou WebM)')
                ->setFormType(VichFileType::class)
                ->setFormTypeOption('allow_delete', false)
                ->setRequired(Crud::PAGE_NEW === $pageName)
                ->onlyOnForms(),
            Field::new('posterFile', 'Poster (image affichée avant la lecture)')
                ->setFormType(VichImageType::class)
                ->setRequired(false)
                ->onlyOnForms(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->hideOnForm(),
        ];
    }

    /**
     * Langues du site, libellées en français : ['Anglais (en)' => 'en', …].
     *
     * @return array<string, string>
     */
    private function localeChoices(): array
    {
        $choices = [];

        foreach (VisitorLocaleResolver::SUPPORTED as $locale) {
            $choices[ucfirst(Languages::getName($locale, 'fr')).' ('.$locale.')'] = $locale;
        }

        return $choices;
    }
}
