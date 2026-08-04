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

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FieldCollection;
use EasyCorp\Bundle\EasyAdminBundle\Collection\FilterCollection;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\SearchDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * HTTP controller for User administration in EasyAdmin.
 *
 * @extends AbstractCrudController<User>
 */
class UserCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function createIndexQueryBuilder(
        SearchDto $searchDto,
        EntityDto $entityDto,
        FieldCollection $fields,
        FilterCollection $filters,
    ): QueryBuilder {
        $queryBuilder = parent::createIndexQueryBuilder($searchDto, $entityDto, $fields, $filters);
        $role = $this->getContext()?->getRequest()->query->get('role');

        if (!\in_array($role, ['ROLE_USER', 'ROLE_AGENCE'], true)) {
            return $queryBuilder;
        }

        $queryBuilder
            ->andWhere('entity.roles LIKE :role')
            ->setParameter('role', \sprintf('%%"%s"%%', $role))
        ;

        if ('ROLE_USER' === $role) {
            $queryBuilder
                ->andWhere('entity.roles NOT LIKE :agencyRole')
                ->setParameter('agencyRole', '%"ROLE_AGENCE"%')
            ;
        }

        return $queryBuilder;
    }

    public function configureFields(string $pageName): iterable
    {
        if ($this->isAgencyView()) {
            if (Crud::PAGE_INDEX === $pageName) {
                return [
                    TextField::new('entreprise', 'Nom de l’agence'),
                    EmailField::new('email', 'E-mail'),
                    TextField::new('telephone', 'Téléphone'),
                    TextField::new('ville', 'Ville'),
                    AssociationField::new('pays', 'Pays'),
                ];
            }

            return [
                IdField::new('id')->hideOnForm(),
                TextField::new('entreprise', 'Agence'),
                TextField::new('nom', 'Nom'),
                TextField::new('prenom', 'Prénom'),
                EmailField::new('email', 'E-mail'),
                TextField::new('telephone', 'Téléphone'),
                TextField::new('whatsApp', 'WhatsApp'),
                TextField::new('emailContact', 'E-mail de contact'),
                TextField::new('numeroContact', 'Numéro de contact'),
                AssociationField::new('pays', 'Pays'),
                AssociationField::new('langues', 'Langue'),
                AssociationField::new('devise', 'Devise'),
                AssociationField::new('fuseauHoraire', 'Fuseau horaire'),
                TextField::new('adresse', 'Adresse'),
                TextField::new('adresseComplement', 'Complément d’adresse'),
                TextField::new('ville', 'Ville'),
                TextField::new('codePostal', 'Code postal'),
                TextField::new('adresseContact', 'Adresse de contact'),
                TextField::new('adresseComplementContact', 'Complément d’adresse de contact'),
                TextField::new('villeContact', 'Ville de contact'),
                TextField::new('paysContact', 'Pays de contact'),
                TextField::new('codePostalContact', 'Code postal de contact'),
                BooleanField::new('isVerified', 'Compte vérifié'),
                BooleanField::new('emailAuthEnabled', 'Authentification par e-mail'),
                IntegerField::new('visitAgency', 'Visites de l’agence'),
                TextField::new('slug')->onlyOnDetail(),
                DateTimeField::new('createdAt', 'Créé le')->onlyOnDetail(),
                DateTimeField::new('updatedAt', 'Mis à jour le')->onlyOnDetail(),
                DateTimeField::new('lastLoginAt', 'Dernière connexion')->onlyOnDetail(),
            ];
        }

        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('nom', 'Nom'),
            TextField::new('prenom', 'Prénom'),
            EmailField::new('email', 'E-mail'),
            TextField::new('password', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->onlyOnForms()
                ->setRequired(Crud::PAGE_NEW === $pageName),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $role = $this->selectedRole();

            if (null !== $role) {
                $entityInstance->setRoles([$role]);
            }

            $this->hashPassword($entityInstance);
        }

        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, object $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $password = $entityInstance->getPassword();

            if (null === $password || '' === $password) {
                $entityInstance->setPassword(
                    $entityManager->getUnitOfWork()->getOriginalEntityData($entityInstance)['password'] ?? null
                );
            } else {
                $this->hashPassword($entityInstance);
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }

    private function selectedRole(): ?string
    {
        $role = $this->getContext()?->getRequest()->query->get('role');

        return \in_array($role, ['ROLE_USER', 'ROLE_AGENCE'], true) ? $role : null;
    }

    private function isAgencyView(): bool
    {
        if ('ROLE_AGENCE' === $this->selectedRole()) {
            return true;
        }

        $entity = $this->getContext()?->getEntity()?->getInstance();

        return $entity instanceof User && \in_array('ROLE_AGENCE', $entity->getRoles(), true);
    }

    private function hashPassword(User $user): void
    {
        $password = $user->getPassword();

        if (null !== $password && '' !== $password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }
    }
}
