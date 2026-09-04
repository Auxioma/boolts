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
use App\Entity\AgencyNotification;
use App\Entity\CategoryBien;
use App\Entity\CategoryBienTransaction;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\User;
use App\Field\PropertyImagesField;
use App\Repository\CategoryBienTransactionRepository;
use App\Service\Import\PropertyCsvImporter;
use App\Service\Import\PropertyImportReport;
use App\Service\Property\AgencyPropertySubmissionMailer;
use App\Service\Property\PropertyNotificationLabeler;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
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
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @extends AbstractCrudController<Property>
 */
class PropertyCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly CategoryBienTransactionRepository $categoryBienTransactionRepository,
        private readonly PropertyNotificationLabeler $propertyNotificationLabeler,
        private readonly AgencyPropertySubmissionMailer $agencyPropertySubmissionMailer,
    ) {
    }

    /**
     * À l'enregistrement d'un bien depuis EasyAdmin : si le statut vient de
     * passer à « Publiée » ou « Refusée », on notifie l'agence propriétaire
     * (message d'acceptation ou de refus, libellé identique au reste du
     * back-office agence).
     */
    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        $becamePublished = false;

        if ($entityInstance instanceof Property) {
            $becamePublished = $this->queueStatusChangeNotification($entityManager, $entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);

        if ($becamePublished && $entityInstance instanceof Property) {
            $agency = $entityInstance->getUser();

            if ($agency instanceof User) {
                $this->agencyPropertySubmissionMailer->sendPublicationNotification($agency, $entityInstance);
            }
        }
    }

    /**
     * Enregistre la notification agence liée au changement de statut et
     * indique si l'annonce vient de passer à « Publiée » (auquel cas
     * {@see updateEntity} déclenche l'e-mail de publication).
     */
    private function queueStatusChangeNotification(
        EntityManagerInterface $entityManager,
        Property $property,
    ): bool {
        $originalData = $entityManager
            ->getUnitOfWork()
            ->getOriginalEntityData($property);

        $previousStatut = $originalData['statut'] ?? null;

        // Selon la version de Doctrine, la donnée d'origine d'une colonne
        // « enumType » peut être l'enum lui-même ou sa valeur scalaire.
        $previousValue = $previousStatut instanceof StatutAnnonceImmobiliere
            ? $previousStatut->value
            : $previousStatut;

        $newStatut = $property->getStatut();

        if ($previousValue === $newStatut->value) {
            return false;
        }

        $message = match ($newStatut) {
            StatutAnnonceImmobiliere::PUBLIEE => $this->propertyNotificationLabeler->acceptedLabel($property),
            StatutAnnonceImmobiliere::REFUSEE => $this->propertyNotificationLabeler->refusedLabel($property),
            default => null,
        };

        if (null === $message) {
            return false;
        }

        $agency = $property->getUser();

        if (!$agency instanceof User) {
            return false;
        }

        $entityManager->persist(
            (new AgencyNotification())
                ->setAgency($agency)
                ->setNom($message)
        );

        return StatutAnnonceImmobiliere::PUBLIEE === $newStatut;
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
            ->addFormTheme('admin/form/property_images.html.twig')
            ->askConfirmationOnBatchActions('Voulez-vous vraiment appliquer l’action « %action_name% » aux %num_items% biens sélectionnés ?');
    }

    public function configureActions(Actions $actions): Actions
    {
        $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::NEW, Action::DELETE)
            ->addBatchAction(
                Action::new('propertyBatchPublish', 'Publier la sélection', 'fa fa-check')
                    ->linkToCrudAction('publishBatch')
                    ->addCssClass('btn btn-success')
            )
            ->addBatchAction(
                Action::new('propertyBatchReject', 'Refuser la sélection', 'fa fa-ban')
                    ->linkToCrudAction('rejectBatch')
                    ->addCssClass('btn btn-warning')
            )
            ->addBatchAction(
                Action::new('propertyBatchDelete', 'Supprimer la sélection', 'fa fa-trash')
                    ->linkToCrudAction('deleteBatch')
                    ->addCssClass('btn btn-danger')
            );

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

        // Ajouté en dernier => affiché en premier, juste à côté de « Vente ».
        $actions->add(
            Crud::PAGE_INDEX,
            Action::new('propertyImportCsv', 'Importer (CSV)', 'fa fa-file-import')
                ->createAsGlobalAction()
                ->linkToCrudAction('importCsv'),
        );

        return $actions;
    }

    /**
     * Suppression en masse des biens sélectionnés dans la liste (action de lot).
     *
     * Les entités sont supprimées une à une via l'EntityManager (et non via
     * une requête SQL groupée) afin que les événements Doctrine s'exécutent
     * normalement (suppression des fichiers d'images liés, etc.).
     *
     * @param BatchActionDto<Property> $batchActionDto
     */
    #[AdminRoute('/delete-batch', name: 'delete_batch', options: ['methods' => ['POST']])]
    public function deleteBatch(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager): Response
    {
        $this->assertValidBatchActionDto($batchActionDto);

        $deletedCount = 0;

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $property = $entityManager->find(Property::class, $entityId);

            if (!$property instanceof Property) {
                continue;
            }

            $entityManager->remove($property);
            ++$deletedCount;
        }

        $entityManager->flush();

        if ($deletedCount > 0) {
            $this->addFlash('success', \sprintf('%d bien%s supprimé%s.', $deletedCount, $deletedCount > 1 ? 's' : '', $deletedCount > 1 ? 's' : ''));
        } else {
            $this->addFlash('warning', 'Aucun bien n’a été supprimé.');
        }

        return $this->redirectToPropertyIndex();
    }

    /**
     * Publication en masse des biens sélectionnés (action de lot) : passe leur
     * statut à « Publiée » et notifie chaque agence, comme lors d'une
     * publication individuelle depuis le formulaire d'édition.
     *
     * @param BatchActionDto<Property> $batchActionDto
     */
    #[AdminRoute('/publish-batch', name: 'publish_batch', options: ['methods' => ['POST']])]
    public function publishBatch(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager): Response
    {
        return $this->changeStatusBatch($batchActionDto, $entityManager, StatutAnnonceImmobiliere::PUBLIEE);
    }

    /**
     * Refus en masse des biens sélectionnés (action de lot) : passe leur
     * statut à « Refusée » et notifie chaque agence.
     *
     * @param BatchActionDto<Property> $batchActionDto
     */
    #[AdminRoute('/reject-batch', name: 'reject_batch', options: ['methods' => ['POST']])]
    public function rejectBatch(BatchActionDto $batchActionDto, EntityManagerInterface $entityManager): Response
    {
        return $this->changeStatusBatch($batchActionDto, $entityManager, StatutAnnonceImmobiliere::REFUSEE);
    }

    /**
     * @param BatchActionDto<Property> $batchActionDto
     */
    private function changeStatusBatch(
        BatchActionDto $batchActionDto,
        EntityManagerInterface $entityManager,
        StatutAnnonceImmobiliere $targetStatut,
    ): Response {
        $this->assertValidBatchActionDto($batchActionDto);

        $updatedProperties = [];

        foreach ($batchActionDto->getEntityIds() as $entityId) {
            $property = $entityManager->find(Property::class, $entityId);

            if (!$property instanceof Property || $targetStatut === $property->getStatut()) {
                continue;
            }

            $property->setStatut($targetStatut);

            $message = match ($targetStatut) {
                StatutAnnonceImmobiliere::PUBLIEE => $this->propertyNotificationLabeler->acceptedLabel($property),
                StatutAnnonceImmobiliere::REFUSEE => $this->propertyNotificationLabeler->refusedLabel($property),
                default => null,
            };

            $agency = $property->getUser();

            if (null !== $message && $agency instanceof User) {
                $entityManager->persist(
                    (new AgencyNotification())
                        ->setAgency($agency)
                        ->setNom($message)
                );
            }

            $updatedProperties[] = $property;
        }

        $entityManager->flush();

        if (StatutAnnonceImmobiliere::PUBLIEE === $targetStatut) {
            foreach ($updatedProperties as $property) {
                $agency = $property->getUser();

                if ($agency instanceof User) {
                    $this->agencyPropertySubmissionMailer->sendPublicationNotification($agency, $property);
                }
            }
        }

        $updatedCount = \count($updatedProperties);

        if ($updatedCount > 0) {
            $this->addFlash(
                'success',
                \sprintf('%d bien%s mis à jour (statut « %s »).', $updatedCount, $updatedCount > 1 ? 's' : '', $targetStatut->label()),
            );
        } else {
            $this->addFlash('warning', 'Aucun bien n’a été mis à jour.');
        }

        return $this->redirectToPropertyIndex();
    }

    /**
     * Vérifie le jeton CSRF et le type d'entité d'une action de lot déclenchée
     * depuis la liste des biens.
     *
     * @param BatchActionDto<Property> $batchActionDto
     */
    private function assertValidBatchActionDto(BatchActionDto $batchActionDto): void
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $expectedCsrfTokenId = 'ea-batch-action-'.$batchActionDto->getName().'-'.$batchActionDto->getEntityFqcn();

        if (!$this->isCsrfTokenValid($expectedCsrfTokenId, $batchActionDto->getCsrfToken())) {
            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if (Property::class !== $batchActionDto->getEntityFqcn()) {
            throw $this->createAccessDeniedException('Type d’entité inattendu.');
        }
    }

    private function redirectToPropertyIndex(): Response
    {
        $indexUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->unset('page')
            ->generateUrl();

        return $this->redirect($indexUrl);
    }

    /**
     * Page d'import CSV de biens immobiliers.
     *
     * GET  : affiche le formulaire (et le lien de téléchargement du modèle) ;
     * GET  + ?download=template : télécharge le modèle CSV vide ;
     * POST : traite le fichier téléversé et affiche le rapport d'import.
     */
    #[AdminRoute('/import-csv', name: 'import_csv', options: ['methods' => ['GET', 'POST']])]
    public function importCsv(
        Request $request,
        PropertyCsvImporter $importer,
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if ('template' === $request->query->get('download')) {
            return $this->streamCsvTemplate($importer);
        }

        $backToIndexUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction(Action::INDEX)
            ->generateUrl();

        $importUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('importCsv')
            ->generateUrl();

        $templateUrl = $this->adminUrlGenerator
            ->setController(self::class)
            ->setAction('importCsv')
            ->set('download', 'template')
            ->generateUrl();

        $report = null;

        if ($request->isMethod('POST')) {
            $report = $this->handleImportUpload($request, $importer);
        }

        return $this->render('admin/property/import.html.twig', [
            'report' => $report,
            'columns' => $importer->templateColumns(),
            'import_url' => $importUrl,
            'template_url' => $templateUrl,
            'back_to_index_url' => $backToIndexUrl,
        ]);
    }

    private function handleImportUpload(Request $request, PropertyCsvImporter $importer): ?PropertyImportReport
    {
        if (!$this->isCsrfTokenValid('property_csv_import', $request->request->getString('_token'))) {
            $this->addFlash('danger', 'Jeton CSRF invalide, veuillez réessayer.');

            return null;
        }

        $file = $request->files->get('csv_file');

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            $this->addFlash('danger', 'Aucun fichier CSV valide n’a été reçu.');

            return null;
        }

        $extension = mb_strtolower((string) $file->getClientOriginalExtension());
        $mimeType = (string) $file->getMimeType();

        $allowedMimes = ['text/csv', 'text/plain', 'application/csv', 'application/vnd.ms-excel', 'application/octet-stream'];

        if ('csv' !== $extension && !\in_array($mimeType, $allowedMimes, true)) {
            $this->addFlash('danger', 'Le fichier doit être un CSV.');

            return null;
        }

        try {
            $report = $importer->import($file->getPathname());
        } catch (\Throwable $exception) {
            $this->addFlash('danger', 'Import impossible : '.$exception->getMessage());

            return null;
        }

        if ($report->getCreated() > 0) {
            $this->addFlash('success', $report->summaryLine());
        } elseif (!$report->hasErrors()) {
            $this->addFlash('warning', 'Aucun bien importé (fichier vide ?).');
        } else {
            $this->addFlash('warning', $report->summaryLine());
        }

        return $report;
    }

    private function streamCsvTemplate(PropertyCsvImporter $importer): StreamedResponse
    {
        $columns = $importer->templateColumns();

        $response = new StreamedResponse(static function () use ($columns): void {
            $output = fopen('php://output', 'w');

            if (false === $output) {
                throw new FileException('Impossible d’ouvrir le flux de sortie CSV.');
            }

            // BOM UTF-8 pour Excel + délimiteur « ; » cohérent avec l'export.
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, $columns, ';');

            fclose($output);
        });

        $response->headers->set('Content-Type', 'text/csv; charset=UTF-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="modele-import-biens.csv"');

        return $response;
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
            IdField::new('id', 'ID')->hideOnForm(),
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
