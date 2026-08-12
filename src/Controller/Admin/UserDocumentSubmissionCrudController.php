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

use App\Entity\Document\UserDocumentRequest;
use App\Entity\Document\UserDocumentSubmission;
use App\Entity\Enum\DocumentSubmissionStatus;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

/**
 * @extends AbstractCrudController<UserDocumentSubmission>
 */
final class UserDocumentSubmissionCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserDocumentSubmission::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('document transmis')
            ->setEntityLabelInPlural('documents transmis')
            ->setPageTitle(Crud::PAGE_INDEX, 'Documents transmis')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail du document transmis')
            ->setDefaultSort(['submittedAt' => 'DESC']);
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
            AssociationField::new('documentRequest', 'Demande')
                ->formatValue(static fn (?UserDocumentRequest $request): string => self::requestLabel($request)),
            TextField::new('originalFileName', 'Nom du fichier'),
            UrlField::new('storagePath', 'Ouvrir le document')
                ->setHelp('Le fichier s’ouvre dans un nouvel onglet.'),
            ChoiceField::new('status', 'Statut')->setChoices([
                'En attente' => DocumentSubmissionStatus::PENDING,
                'Validé' => DocumentSubmissionStatus::APPROVED,
                'Refusé' => DocumentSubmissionStatus::REJECTED,
            ]),
            IntegerField::new('attemptNumber', 'Tentative'),
            TextField::new('mimeType', 'Type MIME')->hideOnIndex(),
            IntegerField::new('fileSize', 'Taille (octets)')->hideOnIndex(),
            TextField::new('checksum', 'Empreinte SHA-256')->onlyOnDetail(),
            TextField::new('fileName', 'Nom de stockage')->onlyOnDetail(),
            TextareaField::new('rejectionReason', 'Motif du refus')->hideOnIndex(),
            AssociationField::new('reviewedBy', 'Vérifié par')
                ->formatValue(static fn (?User $user): string => $user?->getEmail() ?? '')
                ->hideOnIndex(),
            DateTimeField::new('reviewedAt', 'Vérifié le')->hideOnIndex(),
            DateTimeField::new('submittedAt', 'Transmis le'),
        ];
    }

    private static function requestLabel(?UserDocumentRequest $request): string
    {
        if (!$request instanceof UserDocumentRequest) {
            return '';
        }

        $user = $request->getUser();
        $userLabel = $user?->getEntreprise() ?? $user?->getEmail() ?? 'Utilisateur inconnu';
        $documentLabel = $request->getRequiredDocument()?->getName() ?? 'Document inconnu';

        return sprintf('%s — %s', $userLabel, $documentLabel);
    }
}
