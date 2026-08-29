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

use App\Admin\Filter\PropertyTranslationFilter;
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\User;
use App\Field\PropertyImagesField;
use App\Repository\CategoryBienTransactionRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\TextFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;

/**
 * @extends AbstractCrudController<Property>
 */
class PropertyCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CategoryBienTransactionRepository $categoryBienTransactionRepository,
    ) {
    }

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
        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE);

        // Les actions globales sont affichées dans l'ordre inverse de leur ajout ;
        // on ajoute donc Vendu, Location puis Vente pour obtenir Vente | Location | Vendu.
        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('propertyQuickFilterVendu', 'Vendu', 'fa fa-circle-check')
                ->createAsGlobalAction()
                ->linkToUrl($this->statutFilterUrl(StatutAnnonceImmobiliere::VENDUE)),
        );

        $locationUrl = $this->transactionFilterUrl('rental');

        if (null !== $locationUrl) {
            $actions->add(
                Crud::PAGE_INDEX,
                Action::new('propertyQuickFilterLocation', 'Location', 'fa fa-key')
                    ->createAsGlobalAction()
                    ->linkToUrl($locationUrl),
            );
        }

        $venteUrl = $this->transactionFilterUrl('sale');

        if (null !== $venteUrl) {
            $actions->add(
                Crud::PAGE_INDEX,
                Action::new('propertyQuickFilterVente', 'Vente', 'fa fa-tag')
                    ->createAsGlobalAction()
                    ->linkToUrl($venteUrl),
            );
        }

        return $actions;
    }

    /**
     * URL de la liste filtrée sur le type de transaction correspondant
     * au mode de prix donné ('sale' ou 'rental').
     */
    private function transactionFilterUrl(string $priceMode): ?string
    {
        foreach ($this->categoryBienTransactionRepository->findAll() as $transaction) {
            if (self::transactionPriceMode($transaction) !== $priceMode) {
                continue;
            }

            return $this->adminUrlGenerator
                ->unset('role')
                ->setController(self::class)
                ->setAction(Action::INDEX)
                ->set('page', 1)
                ->set('filters', [
                    'typeTransaction' => ['comparison' => '=', 'value' => (string) $transaction->getId()],
                ])
                ->generateUrl();
        }

        return null;
    }

    private function statutFilterUrl(StatutAnnonceImmobiliere $statut): string
    {
        return $this->adminUrlGenerator
            ->unset('role')
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->set('page', 1)
            ->set('filters', [
                'statut' => ['comparison' => '=', 'value' => [$statut->value]],
            ])
            ->generateUrl();
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('typeTransaction', 'Transaction')
                ->setFormTypeOption('value_type_options.choice_label', 'name'))
            ->add(EntityFilter::new('typeBien', 'Type de bien')
                ->setFormTypeOption('value_type_options.choice_label', 'name'))
            ->add(ChoiceFilter::new('statut', 'Statut')
                ->setChoices(self::statutChoices())
                ->canSelectMultiple())
            ->add(PropertyTranslationFilter::new('ville', 'Ville'))
            ->add(PropertyTranslationFilter::new('pays', 'Pays'))
            ->add(TextFilter::new('codePostal', 'Code postal'))
            ->add(NumericFilter::new('prix', 'Prix de vente'))
            ->add(NumericFilter::new('montantLoyerHorsCharge', 'Loyer hors charges'))
            ->add(NumericFilter::new('surfaceTotal', 'Surface (m²)'))
            ->add(NumericFilter::new('chambres', 'Chambres'))
            ->add(NumericFilter::new('salleDeBains', 'Salles de bains'))
            ->add(NumericFilter::new('anneeConstruction', 'Année de construction'))
            ->add(ChoiceFilter::new('dpeLettre', 'DPE')
                ->setChoices(self::energyLetterChoices())
                ->canSelectMultiple())
            ->add(ChoiceFilter::new('gesLettre', 'GES')
                ->setChoices(self::energyLetterChoices())
                ->canSelectMultiple())
            ->add(EntityFilter::new('caracteristique', 'Caractéristiques')
                ->setFormTypeOption('value_type_options.choice_label', 'nom'))
            ->add(EntityFilter::new('user', 'Agence')
                ->setFormTypeOption('value_type_options.choice_label', static fn (User $agency): string => self::agencyLabel($agency))
                ->setFormTypeOption('value_type_options.query_builder', static fn (EntityRepository $repository): QueryBuilder => $repository->createQueryBuilder('agency')
                    ->andWhere('agency.roles LIKE :agencyRole')
                    ->andWhere('agency.deletedAt IS NULL')
                    ->setParameter('agencyRole', '%"ROLE_AGENCE"%')
                    ->orderBy('agency.entreprise', 'ASC')))
            ->add(TextFilter::new('referenceInterne', 'Référence interne'))
            ->add(BooleanFilter::new('showAdresse', 'Adresse affichée'))
            ->add(DateTimeFilter::new('createdAt', 'Créé le'));
    }

    /**
     * @return array<string, string>
     */
    private static function energyLetterChoices(): array
    {
        return array_combine(range('A', 'G'), range('A', 'G'));
    }

    /**
     * Statuts sous forme « libellé => valeur scalaire » pour le filtre.
     *
     * @return array<string, string>
     */
    private static function statutChoices(): array
    {
        return array_map(
            static fn (StatutAnnonceImmobiliere $statut): string => $statut->value,
            StatutAnnonceImmobiliere::choices(),
        );
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                TextField::new('thumbnail', 'Photo')
                    ->setVirtual(true)
                    ->setSortable(false)
                    ->formatValue(static fn (mixed $value, ?Property $property): ?PropertyImage => $property instanceof Property
                        ? self::firstPropertyImage($property)
                        : null)
                    ->setTemplatePath('admin/field/property_thumbnail.html.twig'),
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

    /**
     * Première image du bien (position la plus basse, 0 ou 1 selon les annonces),
     * en ignorant les lignes sans fichier téléversé.
     */
    private static function firstPropertyImage(Property $property): ?PropertyImage
    {
        $images = array_filter(
            $property->getPropertyImages()->toArray(),
            static fn (PropertyImage $image): bool => null !== $image->getImageName() && '' !== $image->getImageName(),
        );

        usort(
            $images,
            static fn (PropertyImage $a, PropertyImage $b): int => (int) $a->getPosition() <=> (int) $b->getPosition(),
        );

        return $images[0] ?? null;
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

        return \sprintf('%d %s%s', $count, $label, 1 === $count ? '' : 's');
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
