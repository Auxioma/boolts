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

use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\User;
use App\Field\PropertyImagesField;
use Doctrine\Common\Collections\Collection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/**
 * @extends AbstractCrudController<Property>
 */
class PropertyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Property::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('bien immobilier')
            ->setEntityLabelInPlural('biens immobiliers')
            ->setPageTitle(Crud::PAGE_INDEX, 'Biens immobiliers')
            ->setPageTitle(Crud::PAGE_EDIT, 'Modifier le statut du bien')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du bien immobilier')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->addFormTheme('admin/form/property_images.html.twig');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id', 'ID'),
                TextField::new('referenceInterne', 'Référence interne'),
                TextField::new('titreDuLogement', 'Titre'),
                AssociationField::new('user', 'Agence')
                    ->formatValue(static fn (?User $agency): string => self::agencyLabel($agency)),
                AssociationField::new('typeBien', 'Type de bien')
                    ->formatValue(static fn (?CategoryBien $type): string => $type?->getName() ?? ''),
                AssociationField::new('typeTransaction', 'Transaction')
                    ->formatValue(static fn (?CategoryBienTransaction $type): string => $type?->getName() ?? ''),
                ChoiceField::new('statut', 'Statut')->setChoices(StatutAnnonceImmobiliere::choices()),
                TextField::new('ville', 'Ville'),
                TextField::new('pays', 'Pays'),
                DateTimeField::new('createdAt', 'Créé le'),
            ];
        }

        return [
            FormField::addTab('Informations générales', 'fa fa-building'),
            IdField::new('id', 'ID'),
            TextField::new('referenceInterne', 'Référence interne')->setColumns(4),
            TextField::new('titreDuLogement', 'Titre')->setColumns(8),
            AssociationField::new('user', 'Agence')
                ->formatValue(static fn (?User $agency): string => self::agencyLabel($agency))
                ->setFormTypeOption('choice_label', static fn (User $agency): string => self::agencyLabel($agency))
                ->setColumns(4),
            AssociationField::new('typeBien', 'Type de bien')
                ->formatValue(static fn (?CategoryBien $type): string => $type?->getName() ?? '')
                ->setFormTypeOption('choice_label', 'name')
                ->setColumns(4),
            AssociationField::new('typeTransaction', 'Transaction')
                ->formatValue(static fn (?CategoryBienTransaction $type): string => $type?->getName() ?? '')
                ->setFormTypeOption('choice_label', 'name')
                ->setFormTypeOption('choice_attr', static fn (CategoryBienTransaction $transaction): array => [
                    'data-price-mode' => self::transactionPriceMode($transaction),
                ])
                ->setFormTypeOption('attr', ['data-property-price-transaction' => 'true'])
                ->setColumns(4),
            ChoiceField::new('statut', 'Statut')->setChoices(StatutAnnonceImmobiliere::choices())->setColumns(4),
            TextareaField::new('descriptionLogement', 'Description')->hideOnIndex()->setColumns(12),

            FormField::addTab('Localisation', 'fa fa-location-dot'),
            TextField::new('adresse', 'Adresse')->hideOnIndex()->setColumns(8),
            TextField::new('codePostal', 'Code postal')->hideOnIndex()->setColumns(4),
            TextField::new('ville', 'Ville')->setColumns(4),
            TextField::new('pays', 'Pays')
                ->setFormTypeOption('attr', ['data-property-energy-country' => 'true'])
                ->setColumns(4),
            BooleanField::new('showAdresse', 'Afficher l’adresse')->hideOnIndex()->setColumns(4),
            TextField::new('region', 'Région')->hideOnIndex()->setColumns(4),
            TextField::new('district', 'District')->hideOnIndex()->setColumns(4),
            TextField::new('neighborhood', 'Quartier')->hideOnIndex()->setColumns(4),
            TextField::new('locality', 'Localité')->hideOnIndex()->setColumns(4),
            TextField::new('poi', 'Point d’intérêt')->hideOnIndex()->setColumns(4),
            TextField::new('fullAddress', 'Adresse complète')->hideOnIndex()->setColumns(12),
            TextField::new('latitude', 'Latitude')->hideOnIndex()->setColumns(6),
            TextField::new('longitude', 'Longitude')->hideOnIndex()->setColumns(6),
            TextField::new('mapboxId', 'ID Mapbox')->hideOnIndex()->setColumns(6),
            TextField::new('featureType', 'Type de localisation')->hideOnIndex()->setColumns(6),
            TextField::new('sessionIdMapbox', 'ID de session Mapbox')->hideOnIndex()->setColumns(12),

            FormField::addTab('Caractéristiques', 'fa fa-sliders'),
            TextField::new('surfaceTotal', 'Surface totale')->hideOnIndex()->setColumns(4),
            TextField::new('chambres', 'Chambres')->hideOnIndex()->setColumns(4),
            TextField::new('salleDeBains', 'Salles de bains')->hideOnIndex()->setColumns(4),
            TextField::new('anneeConstruction', 'Année de construction')->hideOnIndex()->setColumns(4),
            AssociationField::new('caracteristique', 'Caractéristiques')
                ->formatValue(static fn (Collection $characteristics): string => self::collectionCount($characteristics, 'caractéristique'))
                ->setFormTypeOption('choice_label', 'nom')
                ->hideOnIndex()
                ->setColumns(12),

            FormField::addTab('Énergie', 'fa fa-bolt'),
            TextField::new('dpe', 'DPE')->hideOnIndex()->setColumns(4),
            TextField::new('dpeLettre', 'Lettre DPE')->hideOnIndex()->setColumns(4),
            TextField::new('dpeMin', 'DPE minimum')->hideOnIndex()->setColumns(4),
            TextField::new('dpeMax', 'DPE maximum')->hideOnIndex()->setColumns(4),
            TextField::new('ges', 'GES')->hideOnIndex()->setColumns(4),
            TextField::new('gesLettre', 'Lettre GES')->hideOnIndex()->setColumns(4),
            DateField::new('dateIndexationEnergie', 'Date d’indexation énergie')->hideOnIndex()->setColumns(4),

            FormField::addTab('Prix', 'fa fa-money-bill'),
            TextField::new('prix', 'Prix de vente')
                ->hideOnIndex()
                ->addCssClass('property-price-sale')
                ->setColumns(6),
            TextField::new('montantLoyerHorsCharge', 'Loyer hors charges')
                ->hideOnIndex()
                ->addCssClass('property-price-rental')
                ->setColumns(4),
            TextField::new('montantDepotDeGarantie', 'Dépôt de garantie')
                ->hideOnIndex()
                ->addCssClass('property-price-rental')
                ->setColumns(4),
            TextField::new('montantDesCharges', 'Charges')
                ->hideOnIndex()
                ->addCssClass('property-price-rental')
                ->setColumns(4),

            FormField::addTab('Images', 'fa fa-images')->onlyWhenUpdating(),
            PropertyImagesField::new('propertyImages', false)->onlyWhenUpdating(),
            AssociationField::new('propertyImages', 'Images')
                ->formatValue(static fn (Collection $images): string => self::collectionCount($images, 'image'))
                ->onlyOnDetail(),

            FormField::addTab('Suivi technique', 'fa fa-gear'),
            TextField::new('slug', 'Slug')->hideOnIndex()->setColumns(6),
            AssociationField::new('favoris', 'Favoris')
                ->formatValue(static fn (Collection $favorites): string => self::collectionCount($favorites, 'favori'))
                ->onlyOnDetail(),
            AssociationField::new('propertyViews', 'Consultations')
                ->formatValue(static fn (Collection $views): string => self::collectionCount($views, 'consultation'))
                ->onlyOnDetail(),
            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
        ];
    }

    private static function agencyLabel(?User $agency): string
    {
        if (!$agency instanceof User) {
            return '';
        }

        return $agency->getEntreprise() ?? $agency->getEmail() ?? '';
    }

    private static function collectionCount(\Countable $items, string $label): string
    {
        $count = $items->count();

        return sprintf('%d %s%s', $count, $label, 1 === $count ? '' : 's');
    }

    private static function transactionPriceMode(CategoryBienTransaction $transaction): string
    {
        return match ($transaction->getSlug()) {
            'vente', 'sale' => 'sale',
            'location', 'rent' => 'rental',
            default => '',
        };
    }
}
