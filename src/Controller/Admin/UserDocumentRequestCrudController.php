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
use App\Entity\Enum\DocumentRequestStatus;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;

/**
 * @extends AbstractCrudController<UserDocumentRequest>
 */
final class UserDocumentRequestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return UserDocumentRequest::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('demande de document')
            ->setEntityLabelInPlural('demandes de documents')
            ->setPageTitle(Crud::PAGE_INDEX, 'Demandes de documents')
            ->setPageTitle(Crud::PAGE_NEW, 'Demander un document')
            ->setPageTitle(Crud::PAGE_DETAIL, 'Détail de la demande')
            ->setDefaultSort(['createdAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->disable(Action::EDIT, Action::DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        $user = AssociationField::new('user', 'Utilisateur')
            ->formatValue(static fn (?User $user): string => self::userLabel($user))
            ->setFormTypeOption('choice_label', static fn (User $user): string => self::userLabel($user));
        $requiredDocument = AssociationField::new('requiredDocument', 'Document demandé');

        if (Crud::PAGE_NEW === $pageName) {
            return [$user, $requiredDocument];
        }

        return [
            IdField::new('id', 'ID'),
            $user,
            $requiredDocument,
            ChoiceField::new('status', 'Statut')->setChoices([
                'En attente d’envoi' => DocumentRequestStatus::WAITING_UPLOAD,
                'En cours de vérification' => DocumentRequestStatus::UNDER_REVIEW,
                'Refusé' => DocumentRequestStatus::REJECTED,
                'Validé' => DocumentRequestStatus::APPROVED,
                'Bloqué' => DocumentRequestStatus::BLOCKED,
            ]),
            BooleanField::new('blocked', 'Bloquée'),
            TextareaField::new('blockedReason', 'Motif du blocage')->hideOnIndex(),
            IntegerField::new('submissionCount', 'Nombre d’envois'),
            IntegerField::new('remainingSubmissionCount', 'Envois restants')->hideOnIndex(),
            DateTimeField::new('blockedAt', 'Bloquée le')->hideOnIndex(),
            DateTimeField::new('completedAt', 'Validée le')->hideOnIndex(),
            DateTimeField::new('createdAt', 'Créée le'),
            DateTimeField::new('updatedAt', 'Mise à jour le')->hideOnIndex(),
        ];
    }

    private static function userLabel(?User $user): string
    {
        if (!$user instanceof User) {
            return '';
        }

        $label = $user->getEntreprise()
            ?? mb_trim(($user->getPrenom() ?? '').' '.($user->getNom() ?? ''));

        return '' !== $label
            ? $label
            : ($user->getEmail() ?? \sprintf('Utilisateur #%d', $user->getId()));
    }
}
