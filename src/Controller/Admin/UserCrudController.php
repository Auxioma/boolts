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

use App\Entity\Booster\PropertyBoost;
use App\Entity\Document\UserDocumentRequest;
use App\Entity\Document\UserDocumentSubmission;
use App\Entity\Enum\DocumentRequestStatus;
use App\Entity\Enum\StatutAnnonceImmobiliere;
use App\Entity\User;
use App\Field\AgencyBoostsField;
use App\Field\AgencyPaymentsField;
use App\Field\UserDocumentsField;
use App\Repository\Billing\AgencySubscriptionPeriodRepository;
use App\Repository\Billing\AgencySubscriptionRepository;
use App\Repository\Billing\PaymentRepository;
use App\Repository\Booster\PropertyBoostRepository;
use App\Repository\Document\RequiredDocumentRepository;
use App\Repository\PropertyViewRepository;
use App\Service\Booster\AdminBoostManager;
use App\Service\Document\ClientDocumentNotificationMailer;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Administration séparée des visiteurs et des agences.
 *
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    private const ROLE_VISITOR = 'ROLE_USER';
    private const ROLE_AGENCY = 'ROLE_AGENCE';

    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly AdminUrlGenerator $adminUrlGenerator,
        private readonly RequiredDocumentRepository $requiredDocumentRepository,
        private readonly AgencySubscriptionRepository $agencySubscriptionRepository,
        private readonly AgencySubscriptionPeriodRepository $agencySubscriptionPeriodRepository,
        private readonly PaymentRepository $paymentRepository,
        private readonly PropertyBoostRepository $propertyBoostRepository,
        private readonly PropertyViewRepository $propertyViewRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        $agencyView = $this->isAgencyView();

        return $crud
            ->setEntityLabelInSingular($agencyView ? 'Agence' : 'Visiteur')
            ->setEntityLabelInPlural($agencyView ? 'Agences' : 'Visiteurs')
            ->setPageTitle(Crud::PAGE_INDEX, $agencyView ? 'Agences' : 'Visiteurs')
            ->setPageTitle(Crud::PAGE_NEW, $agencyView ? 'Ajouter une agence' : 'Ajouter un visiteur')
            ->setPageTitle(Crud::PAGE_EDIT, $agencyView ? 'Modifier l’agence' : 'Modifier le visiteur')
            ->setPageTitle(Crud::PAGE_DETAIL, $agencyView ? 'Détail de l’agence' : 'Détail du visiteur')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->addFormTheme('admin/form/user_documents.html.twig');
    }

    public function configureAssets(Assets $assets): Assets
    {
        return $assets
            ->addJsFile('js/admin-user-documents.js')
            ->addJsFile('js/admin-agency-boosts.js');
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions->disable(Action::BATCH_DELETE);
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $role = $this->selectedRole();
        $queryBuilder->andWhere('entity.deletedAt IS NULL');

        if (self::ROLE_AGENCY === $role) {
            return $queryBuilder
                ->andWhere('entity.roles LIKE :agencyRole')
                ->setParameter('agencyRole', '%"'.self::ROLE_AGENCY.'"%');
        }

        if (self::ROLE_VISITOR === $role) {
            // Un visiteur peut avoir ROLE_USER enregistré ou un tableau de rôles vide,
            // car User::getRoles() ajoute déjà ROLE_USER automatiquement.
            return $queryBuilder
                ->andWhere('entity.roles NOT LIKE :agencyRole')
                ->setParameter('agencyRole', '%"'.self::ROLE_AGENCY.'"%');
        }

        return $queryBuilder;
    }

    public function deleteEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if (!$entityInstance instanceof User) {
            parent::deleteEntity($entityManager, $entityInstance);

            return;
        }

        $currentUser = $this->getUser();

        if ($currentUser instanceof User && $currentUser->getId() === $entityInstance->getId()) {
            $this->addFlash('warning', 'Vous ne pouvez pas supprimer votre propre compte depuis cette page.');

            return;
        }

        foreach ($entityInstance->getProperties() as $property) {
            $property->setStatut(StatutAnnonceImmobiliere::SUPPRIMEE);
        }

        $entityInstance->softDelete();
        $entityManager->flush();
        $this->addFlash('success', 'Le compte a été supprimé.');
    }

    public function configureFields(string $pageName): iterable
    {
        return $this->isAgencyView()
            ? $this->agencyFields($pageName)
            : $this->visitorFields($pageName);
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function visitorFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id', 'ID'),
                TextField::new('nom', 'Nom'),
                TextField::new('prenom', 'Prénom'),
                EmailField::new('email', 'E-mail'),
                TextField::new('telephone', 'Téléphone'),
                TextField::new('ville', 'Ville'),
                AssociationField::new('pays', 'Pays'),
                BooleanField::new('isVerified', 'Vérifié'),
                DateTimeField::new('createdAt', 'Inscrit le'),
            ];
        }

        return [
            IdField::new('id', 'ID')->hideOnForm(),
            FormField::addTab('Identité', 'fa fa-user'),
            TextField::new('nom', 'Nom')->setColumns(6),
            TextField::new('prenom', 'Prénom')->setColumns(6),
            EmailField::new('email', 'E-mail')->setColumns(6),
            TextField::new('telephone', 'Téléphone')->setColumns(6),
            TextField::new('whatsApp', 'WhatsApp')->setColumns(6),
            $this->passwordField($pageName)->setColumns(6),
            FormField::addTab('Adresse', 'fa fa-location-dot'),
            TextField::new('adresse', 'Adresse')->setColumns(8),
            TextField::new('adresseComplement', 'Complément')->setColumns(4),
            TextField::new('codePostal', 'Code postal')->setColumns(4),
            TextField::new('ville', 'Ville')->setColumns(4),
            AssociationField::new('pays', 'Pays')->setColumns(4),
            FormField::addTab('Préférences', 'fa fa-sliders'),
            AssociationField::new('langues', 'Langue')->setColumns(4),
            AssociationField::new('devise', 'Devise')->setColumns(4),
            AssociationField::new('fuseauHoraire', 'Fuseau horaire')->setColumns(4),
            FormField::addTab('Sécurité', 'fa fa-shield-halved'),
            BooleanField::new('isVerified', 'Compte vérifié')->setColumns(6),
            BooleanField::new('emailAuthEnabled', 'Authentification par e-mail')->setColumns(6),
            FormField::addTab('Documents', 'fa fa-file-lines')->onlyWhenUpdating(),
            UserDocumentsField::new('documentRequests', false)->onlyWhenUpdating(),

            DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
            DateTimeField::new('lastLoginAt', 'Dernière connexion')->onlyOnDetail(),
        ];
    }

    /**
     * @return iterable<FieldInterface>
     */
    private function agencyFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            return [
                IdField::new('id', 'ID'),
                TextField::new('entreprise', 'Nom de l’agence'),
                EmailField::new('email', 'Compte administrateur'),
                EmailField::new('emailContact', 'E-mail public'),
                TextField::new('numeroContact', 'Téléphone public'),
                TextField::new('villeContact', 'Ville'),
                BooleanField::new('isVerified', 'Vérifiée'),
                IntegerField::new('visitAgency', 'Visites'),
            ];
        }

        return [
            IdField::new('id', 'ID')->hideOnForm(),
            FormField::addTab('Agence', 'fa fa-building'),
            TextField::new('entreprise', 'Nom de l’agence')->setColumns(8),
            TextField::new('slug', 'Slug')->onlyOnDetail(),
            TextareaField::new('description', 'Description de l’agence')
                ->setColumns(12)
                ->hideOnIndex(),
            FormField::addTab('Responsable du compte', 'fa fa-user-tie'),
            TextField::new('nom', 'Nom')->setColumns(6),
            TextField::new('prenom', 'Prénom')->setColumns(6),
            EmailField::new('email', 'E-mail de connexion')->setColumns(6),
            TextField::new('telephone', 'Téléphone personnel')->setColumns(6),
            TextField::new('whatsApp', 'WhatsApp')->setColumns(6),
            $this->passwordField($pageName)->setColumns(6),
            FormField::addTab('Coordonnées publiques', 'fa fa-address-card'),
            EmailField::new('emailContact', 'E-mail de contact')->setColumns(6),
            TextField::new('numeroContact', 'Numéro de contact')->setColumns(6),
            TextField::new('adresseContact', 'Adresse de contact')->setColumns(8),
            TextField::new('adresseComplementContact', 'Complément')->setColumns(4),
            TextField::new('codePostalContact', 'Code postal')->setColumns(4),
            TextField::new('villeContact', 'Ville')->setColumns(4),
            TextField::new('paysContact', 'Pays')->setColumns(4),
            FormField::addTab('Adresse du compte', 'fa fa-location-dot'),
            TextField::new('adresse', 'Adresse')->setColumns(8),
            TextField::new('adresseComplement', 'Complément')->setColumns(4),
            TextField::new('codePostal', 'Code postal')->setColumns(4),
            TextField::new('ville', 'Ville')->setColumns(4),
            AssociationField::new('pays', 'Pays')->setColumns(4),
            FormField::addTab('Préférences', 'fa fa-sliders'),
            AssociationField::new('langues', 'Langue')->setColumns(4),
            AssociationField::new('devise', 'Devise')->setColumns(4),
            AssociationField::new('fuseauHoraire', 'Fuseau horaire')->setColumns(4),
            FormField::addTab('Sécurité et statistiques', 'fa fa-shield-halved'),
            BooleanField::new('isVerified', 'Compte vérifié')->setColumns(4),
            BooleanField::new('emailAuthEnabled', 'Authentification par e-mail')->setColumns(4),
            FormField::addTab('Documents', 'fa fa-file-lines')->onlyWhenUpdating(),
            UserDocumentsField::new('documentRequests', false)->onlyWhenUpdating(),
            FormField::addTab('Paiements', 'fa fa-credit-card')->onlyWhenUpdating(),
            $this->agencyPaymentsField()->onlyWhenUpdating(),
            FormField::addTab('Boosts', 'fa fa-rocket')->onlyWhenUpdating(),
            $this->agencyBoostsField()->onlyWhenUpdating(),

            DateTimeField::new('createdAt', 'Créée le')->onlyOnDetail(),
            DateTimeField::new('updatedAt', 'Mise à jour le')->onlyOnDetail(),
            DateTimeField::new('lastLoginAt', 'Dernière connexion')->onlyOnDetail(),
        ];
    }

    private function agencyPaymentsField(): AgencyPaymentsField
    {
        $agency = $this->getContext()?->getEntity()?->getInstance();
        $data = $agency instanceof User
            ? $this->agencyPaymentsData($agency)
            : $this->emptyAgencyPaymentsData();

        return AgencyPaymentsField::new('agencyPayments', false)
            ->setFormTypeOption('data', $data)
            ->formatValue(fn (mixed $value, ?User $agency): array => $agency instanceof User
                ? $this->agencyPaymentsData($agency)
                : $this->emptyAgencyPaymentsData());
    }

    private function agencyBoostsField(): AgencyBoostsField
    {
        $agency = $this->getContext()?->getEntity()?->getInstance();
        $boosts = $agency instanceof User
            ? $this->propertyBoostRepository->findActiveForAgency($agency)
            : [];

        return AgencyBoostsField::new('agencyBoosts', false)
            ->setFormTypeOption('data', $boosts)
            ->setFormTypeOption('boost_metrics', $this->boostMetrics($boosts))
            ->formatValue(fn (mixed $value, ?User $agency): array => $agency instanceof User
                ? $this->propertyBoostRepository->findActiveForAgency($agency)
                : []);
    }

    /**
     * Vues de l'annonce sur la fenêtre du boost et lien vers la fiche du bien.
     *
     * @param list<PropertyBoost> $boosts
     *
     * @return array<int, array{views: int, editUrl: string}>
     */
    private function boostMetrics(array $boosts): array
    {
        $metrics = [];

        foreach ($boosts as $boost) {
            $property = $boost->getProperty();

            $metrics[$boost->getId()] = [
                'views' => $this->propertyViewRepository->countByPropertyBetween(
                    $property,
                    $boost->getStartsAt(),
                    $boost->getEndsAt(),
                ),
                'editUrl' => $this->adminUrlGenerator
                    ->unset('role')
                    ->setController(PropertyCrudController::class)
                    ->setAction(Action::EDIT)
                    ->setEntityId($property->getId())
                    ->generateUrl(),
            ];
        }

        return $metrics;
    }

    /**
     * @return array{
     *     currentSubscription: object|null,
     *     periods: list<object>,
     *     totals: list<array{amountMinor: int, currencyName: ?string, currencySign: ?string}>
     * }
     */
    private function agencyPaymentsData(User $agency): array
    {
        return [
            'currentSubscription' => $this->agencySubscriptionRepository->findCurrentForAgency($agency),
            'periods' => $this->agencySubscriptionPeriodRepository->findForAgency($agency),
            'totals' => $this->paymentRepository->sumNetPaidByCurrencyForAgency($agency),
        ];
    }

    /**
     * @return array{
     *     currentSubscription: null,
     *     periods: list<object>,
     *     totals: list<array{amountMinor: int, currencyName: ?string, currencySign: ?string}>
     * }
     */
    private function emptyAgencyPaymentsData(): array
    {
        return [
            'currentSubscription' => null,
            'periods' => [],
            'totals' => [],
        ];
    }

    private function passwordField(string $pageName): TextField
    {
        return TextField::new('password', 'Mot de passe')
            ->setFormType(PasswordType::class)
            ->onlyOnForms()
            ->setRequired(Crud::PAGE_NEW === $pageName)
            ->setHelp(
                Crud::PAGE_EDIT === $pageName
                    ? 'Laissez vide pour conserver le mot de passe actuel.'
                    : null
            );
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $role = $this->selectedRole();

            if (null !== $role) {
                $entityInstance->setRoles([$role]);
            }

            $this->hashPassword($entityInstance);
            $this->addMissingDocumentRequests($entityInstance, $entityManager);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function edit(AdminContext $context): KeyValueStore|Response
    {
        $entity = $context->getEntity()->getInstance();

        if ($entity instanceof User && $this->addMissingDocumentRequests($entity, $this->entityManager)) {
            $this->entityManager->flush();
        }

        return parent::edit($context);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $password = $entityInstance->getPassword();

            if (null === $password || '' === $password) {
                $originalData = $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance);
                $entityInstance->setPassword($originalData['password'] ?? null);
            } else {
                $this->hashPassword($entityInstance);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    #[Route(
        '/admin/user/{user}/document/{submission}/approve',
        name: 'admin_user_document_approve',
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function approveDocument(
        User $user,
        UserDocumentSubmission $submission,
        Request $request,
        EntityManagerInterface $entityManager,
        ClientDocumentNotificationMailer $clientDocumentNotificationMailer,
    ): Response {
        $documentRequest = $this->documentRequestForUser($user, $submission);
        $tokenId = 'approve_document_'.$submission->getId();

        if (!$this->isCsrfTokenValid($tokenId, $request->request->getString('_approve_token_'.$submission->getId()))) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], Response::HTTP_FORBIDDEN);
            }

            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        if ($submission !== $documentRequest->getLatestSubmission() || 'pending' !== $submission->getStatus()->value) {
            return $this->documentActionResponse(
                $request,
                $user,
                false,
                'Ce document ne peut plus être validé.',
                Response::HTTP_CONFLICT,
            );
        }

        $administrator = $this->getUser();

        if (!$administrator instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $submission->approve($administrator);
        $documentRequest->markAsCompleted();
        $entityManager->flush();

        $clientDocumentNotificationMailer->sendApprovedDocumentNotification($user, $submission);

        return $this->documentActionResponse(
            $request,
            $user,
            true,
            'Le document a été validé.',
            Response::HTTP_OK,
            ['status' => 'approved', 'statusLabel' => 'Document validé'],
        );
    }

    #[Route(
        '/admin/user/{user}/document/{submission}/reject',
        name: 'admin_user_document_reject',
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function rejectDocument(
        User $user,
        UserDocumentSubmission $submission,
        Request $request,
        EntityManagerInterface $entityManager,
        ClientDocumentNotificationMailer $clientDocumentNotificationMailer,
    ): Response {
        $documentRequest = $this->documentRequestForUser($user, $submission);
        $tokenId = 'reject_document_'.$submission->getId();

        if (!$this->isCsrfTokenValid($tokenId, $request->request->getString('_reject_token_'.$submission->getId()))) {
            if ($request->isXmlHttpRequest()) {
                return $this->json(['success' => false, 'message' => 'Jeton CSRF invalide.'], Response::HTTP_FORBIDDEN);
            }

            throw $this->createAccessDeniedException('Jeton CSRF invalide.');
        }

        $reason = mb_trim($request->request->getString('rejection_reason_'.$submission->getId()));

        if ('' === $reason) {
            return $this->documentActionResponse(
                $request,
                $user,
                false,
                'Le motif du refus est obligatoire.',
                Response::HTTP_UNPROCESSABLE_ENTITY,
            );
        }

        if ($submission !== $documentRequest->getLatestSubmission() || 'pending' !== $submission->getStatus()->value) {
            return $this->documentActionResponse(
                $request,
                $user,
                false,
                'Ce document ne peut plus être refusé.',
                Response::HTTP_CONFLICT,
            );
        }

        $administrator = $this->getUser();

        if (!$administrator instanceof User) {
            throw $this->createAccessDeniedException();
        }

        $submission->reject($administrator, $reason);
        $documentRequest->setStatus(DocumentRequestStatus::REJECTED);
        $entityManager->flush();

        $clientDocumentNotificationMailer->sendRejectedDocumentNotification($user, $submission);

        return $this->documentActionResponse(
            $request,
            $user,
            true,
            'Le document a été refusé.',
            Response::HTTP_OK,
            [
                'status' => 'rejected',
                'statusLabel' => 'Refusé',
                'rejectionReason' => $reason,
            ],
        );
    }

    private function documentRequestForUser(
        User $user,
        UserDocumentSubmission $submission,
    ): UserDocumentRequest {
        $documentRequest = $submission->getDocumentRequest();

        if (!$documentRequest instanceof UserDocumentRequest || $documentRequest->getUser()?->getId() !== $user->getId()) {
            throw $this->createNotFoundException();
        }

        return $documentRequest;
    }

    #[Route(
        '/admin/agency/{user}/boost/{boost}/cancel',
        name: 'admin_agency_boost_cancel',
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function cancelAgencyBoost(
        User $user,
        PropertyBoost $boost,
        Request $request,
        AdminBoostManager $adminBoostManager,
    ): Response {
        $this->assertBoostBelongsToAgency($user, $boost);

        if (!$this->isCsrfTokenValid(
            'cancel_agency_boost_'.$boost->getId(),
            $request->request->getString('_boost_token_'.$boost->getId()),
        )) {
            return $this->boostActionResponse($request, $user, false, 'Jeton CSRF invalide.', Response::HTTP_FORBIDDEN);
        }

        $adminBoostManager->cancel($boost);

        return $this->boostActionResponse($request, $user, true, 'Le boost a été effacé.', Response::HTTP_OK);
    }

    #[Route(
        '/admin/agency/{user}/boost/{boost}/refund',
        name: 'admin_agency_boost_refund',
        methods: ['POST'],
    )]
    #[IsGranted('ROLE_ADMIN')]
    public function refundAgencyBoost(
        User $user,
        PropertyBoost $boost,
        Request $request,
        AdminBoostManager $adminBoostManager,
    ): Response {
        $this->assertBoostBelongsToAgency($user, $boost);

        if (!$this->isCsrfTokenValid(
            'refund_agency_boost_'.$boost->getId(),
            $request->request->getString('_boost_token_'.$boost->getId()),
        )) {
            return $this->boostActionResponse($request, $user, false, 'Jeton CSRF invalide.', Response::HTTP_FORBIDDEN);
        }

        $adminBoostManager->cancelAndRefund($boost);

        return $this->boostActionResponse(
            $request,
            $user,
            true,
            'Le boost a été effacé et recrédité à l’agence.',
            Response::HTTP_OK,
        );
    }

    private function assertBoostBelongsToAgency(User $user, PropertyBoost $boost): void
    {
        if (
            !\in_array(self::ROLE_AGENCY, $user->getRoles(), true)
            || $boost->getAgency()->getId() !== $user->getId()
        ) {
            throw $this->createNotFoundException();
        }
    }

    private function boostActionResponse(
        Request $request,
        User $user,
        bool $success,
        string $message,
        int $status,
    ): Response {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => $success,
                'message' => $message,
            ], $status);
        }

        $this->addFlash($success ? 'success' : 'warning', $message);

        return $this->redirectToUserEdit($user);
    }

    private function redirectToUserEdit(User $user): RedirectResponse
    {
        $role = \in_array(self::ROLE_AGENCY, $user->getRoles(), true)
            ? self::ROLE_AGENCY
            : self::ROLE_VISITOR;

        return $this->redirect(
            $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction(Action::EDIT)
                ->setEntityId($user->getId())
                ->set('role', $role)
                ->generateUrl(),
        );
    }

    /**
     * @param array<string, scalar|null> $data
     */
    private function documentActionResponse(
        Request $request,
        User $user,
        bool $success,
        string $message,
        int $status,
        array $data = [],
    ): Response {
        if ($request->isXmlHttpRequest()) {
            return $this->json([
                'success' => $success,
                'message' => $message,
                ...$data,
            ], $status);
        }

        $this->addFlash($success ? 'success' : 'warning', $message);

        return $this->redirectToUserEdit($user);
    }

    private function selectedRole(): ?string
    {
        $role = $this->getContext()?->getRequest()->query->get('role');

        return \in_array($role, [self::ROLE_VISITOR, self::ROLE_AGENCY], true)
            ? $role
            : null;
    }

    private function isAgencyView(): bool
    {
        if (self::ROLE_AGENCY === $this->selectedRole()) {
            return true;
        }

        $entity = $this->getContext()?->getEntity()?->getInstance();

        return $entity instanceof User
            && \in_array(self::ROLE_AGENCY, $entity->getRoles(), true);
    }

    private function hashPassword(User $user): void
    {
        $password = $user->getPassword();

        if (null !== $password && '' !== $password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }
    }

    private function addMissingDocumentRequests(User $user, EntityManagerInterface $entityManager): bool
    {
        if (!\in_array(self::ROLE_AGENCY, $user->getRoles(), true)) {
            return false;
        }

        $existingDocumentIds = [];

        foreach ($user->getDocumentRequests() as $documentRequest) {
            $requiredDocumentId = $documentRequest->getRequiredDocument()?->getId();

            if (null !== $requiredDocumentId) {
                $existingDocumentIds[$requiredDocumentId] = true;
            }
        }

        $added = false;
        $requiredDocuments = $this->requiredDocumentRepository->findBy(
            ['enabled' => true],
            ['position' => 'ASC', 'name' => 'ASC'],
        );

        foreach ($requiredDocuments as $requiredDocument) {
            $requiredDocumentId = $requiredDocument->getId();

            if (null === $requiredDocumentId || isset($existingDocumentIds[$requiredDocumentId])) {
                continue;
            }

            $documentRequest = (new UserDocumentRequest())
                ->setRequiredDocument($requiredDocument);
            $user->addDocumentRequest($documentRequest);
            $entityManager->persist($documentRequest);
            $added = true;
        }

        return $added;
    }
}
