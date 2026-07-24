<?php

/**
 * Copyright(c) 2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency et Pastelit Co.
 * Toute reproduction, modification, distribution ou utilisation sans autorisation préalable est interdite.
 */

namespace App\Controller\Dashboard\Api\AgenceImmobiliere;

use App\Entity\HoraireOuverture;
use App\Entity\User;
use App\Form\Dashboard\AgenceImmobiliere\ProfileAgenceType;
use App\Repository\LangueParlerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

/**
 * HTTP controller for module Dashboard / Api / AgenceImmobiliere / UpdateProfileAgenceImmobiliereController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class UpdateProfileAgenceImmobiliereController extends AbstractController
{
    #[Route('/dashboard/api/profile', name: 'api_profile_agence_immobiliere', methods: ['POST'])]
    /**
     * Handles the index controller action.
     */
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordHasherInterface $passwordHasher,
        LangueParlerRepository $langueParlerRepository,
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non connecté.',
            ], 401);
        }

        $imageFile = $request->files->get('imageFile');

        if (!$imageFile) {
            $formFiles = $request->files->all();
            $imageFile = $formFiles['profile_agence']['imageFile'] ?? null;
        }

        if ($imageFile) {
            $user->setImageFile($imageFile);

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Les modifications ont été effectuées avec succès !',
                'imageName' => $user->getImageName(),
                'imageSize' => $user->getImageSize(),
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            return $this->json([
                'success' => false,
                'message' => 'Un problème est survenu avec les modifications. Réessayez.',
            ], 400);
        }

        $csrfToken = new CsrfToken('profile_edit', $data['_token'] ?? '');

        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Un problème est survenu avec les modifications. Réessayez.',
            ], 419);
        }

        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if ('plainPassword' === $field) {
            if (!\is_array($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Mot de passe invalide.',
                ], 422);
            }

            $password = $value['password'] ?? null;
            $passwordConfirm = $value['passwordConfirm'] ?? null;

            if (!$password || !$passwordConfirm) {
                return $this->json([
                    'success' => false,
                    'message' => 'Veuillez remplir les deux champs.',
                ], 422);
            }

            if ($password !== $passwordConfirm) {
                return $this->json([
                    'success' => false,
                    'message' => 'Les mots de passe ne correspondent pas.',
                ], 422);
            }

            if (mb_strlen($password) < 12) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir au moins 12 caractères.',
                ], 422);
            }

            $user->setPassword(
                $passwordHasher->hashPassword($user, $password)
            );

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Les modifications ont été effectuées avec succès !',
                'field' => $field,
                'value' => '*****************',
            ]);
        }

        if (\in_array($field, ['horaireOuvertures', 'openingHours', 'horaireOuverture'], true)) {
            if (!\is_array($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Les horaires sont invalides.',
                ], 422);
            }

            if (!$this->hasAtLeastOneOpeningHour($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Veuillez renseigner au moins un horaire.',
                ], 422);
            }

            $this->updateHoraireOuvertures($user, $value, $entityManager);

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Les horaires ont été enregistrés avec succès !',
                'field' => $field,
                'value' => $value,
            ]);
        }

        if ('langueParlers' === $field) {
            if (!\is_array($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Les langues sélectionnées sont invalides.',
                ], 422);
            }

            foreach ($user->getLangueParlers()->toArray() as $langueParler) {
                $user->removeLangueParler($langueParler);
            }

            foreach ($value as $langueId) {
                if (!$langueId || !is_numeric($langueId)) {
                    continue;
                }

                $langueParler = $langueParlerRepository->find((int) $langueId);

                if ($langueParler) {
                    $user->addLangueParler($langueParler);
                }
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Les langues parlées ont été enregistrées avec succès !',
                'field' => $field,
                'value' => $value,
            ]);
        }

        $form = $this->createForm(ProfileAgenceType::class, $user);

        if (\in_array($field, ['adresse', 'adresseContact'], true)) {
            if (!\is_array($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Adresse invalide.',
                ], 422);
            }

            if ('adresse' === $field) {
                $form->submit([
                    'adresse' => $value['adresse'] ?? null,
                    'adresseComplement' => $value['adresseComplement'] ?? null,
                    'codePostal' => $value['codePostal'] ?? null,
                    'ville' => $value['ville'] ?? null,
                    'pays' => $value['pays'] ?? null,
                ], false);
            }

            if ('adresseContact' === $field) {
                $form->submit([
                    'adresseContact' => $value['adresseContact'] ?? null,
                    'codePostalContact' => $value['codePostalContact'] ?? null,
                    'villeContact' => $value['villeContact'] ?? null,
                    'paysContact' => $value['paysContact'] ?? null,
                ], false);
            }

            if (!$form->isValid()) {
                return $this->json([
                    'success' => false,
                    'message' => $this->getFirstFormError($form),
                    'errors' => $this->getFormErrors($form),
                ], 422);
            }

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Les modifications ont été effectuées avec succès !',
                'field' => $field,
                'value' => $value,
            ]);
        }

        if (!$field || !$form->has($field)) {
            return $this->json([
                'success' => false,
                'message' => 'Un problème est survenu avec les modifications. Réessayez.',
            ], 400);
        }

        $submitData = [
            $field => $value,
        ];

        if ('numeroContact' === $field && \array_key_exists('whatsApp', $data)) {
            $submitData['whatsApp'] = $data['whatsApp'];
        }

        $form->submit($submitData, false);

        if (!$form->isValid()) {
            return $this->json([
                'success' => false,
                'message' => $this->getFirstFormError($form),
                'errors' => $this->getFormErrors($form),
            ], 422);
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Les modifications ont été effectuées avec succès !',
            'field' => $field,
            'value' => $value,
            'whatsApp' => $data['whatsApp'] ?? null,
        ]);
    }

    private function hasAtLeastOneOpeningHour(array $openingHours): bool
    {
        foreach ($openingHours as $dayData) {
            if (!\is_array($dayData)) {
                continue;
            }

            if (!empty($dayData['ouvertureMatin'])) {
                return true;
            }

            if (!empty($dayData['fermetureMatin'])) {
                return true;
            }

            if (!empty($dayData['ouvertureApresMidi'])) {
                return true;
            }

            if (!empty($dayData['fermetureApresMidi'])) {
                return true;
            }
        }

        return false;
    }

    private function updateHoraireOuvertures(
        User $user,
        array $openingHours,
        EntityManagerInterface $entityManager,
    ): void {
        $days = [
            'lundi',
            'mardi',
            'mercredi',
            'jeudi',
            'vendredi',
            'samedi',
            'dimanche',
        ];

        foreach ($days as $index => $day) {
            $dayData = $openingHours[$day] ?? $openingHours[$index] ?? [];

            if (!\is_array($dayData)) {
                $dayData = [];
            }

            $horaireOuverture = $this->findHoraireOuvertureByJour($user, $day);

            if (!$horaireOuverture) {
                $horaireOuverture = new HoraireOuverture();
                $horaireOuverture->setJour($day);
                $horaireOuverture->setAgence($user);

                $user->addHoraireOuverture($horaireOuverture);

                $entityManager->persist($horaireOuverture);
            }

            $hasHourForThisDay =
                !empty($dayData['ouvertureMatin'])
                || !empty($dayData['fermetureMatin'])
                || !empty($dayData['ouvertureApresMidi'])
                || !empty($dayData['fermetureApresMidi']);

            $horaireOuverture->setIsOpen(
                !empty($dayData['isOpen']) || $hasHourForThisDay
            );

            $horaireOuverture->setOuvertureMatin(
                $this->toTime($dayData['ouvertureMatin'] ?? null)
            );

            $horaireOuverture->setFermetureMatin(
                $this->toTime($dayData['fermetureMatin'] ?? null)
            );

            $horaireOuverture->setOuvertureApresMidi(
                $this->toTime($dayData['ouvertureApresMidi'] ?? null)
            );

            $horaireOuverture->setFermetureApresMidi(
                $this->toTime($dayData['fermetureApresMidi'] ?? null)
            );
        }
    }

    private function findHoraireOuvertureByJour(User $user, string $jour): ?HoraireOuverture
    {
        foreach ($user->getHoraireOuvertures() as $horaireOuverture) {
            if ($horaireOuverture->getJour() === $jour) {
                return $horaireOuverture;
            }
        }

        return null;
    }

    private function toTime(?string $value): ?\DateTimeInterface
    {
        if (!$value) {
            return null;
        }

        $time = \DateTime::createFromFormat('H:i', $value);

        if (!$time instanceof \DateTimeInterface) {
            return null;
        }

        return $time;
    }

    private function getFormErrors(FormInterface $form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    private function getFirstFormError(FormInterface $form): string
    {
        $errors = $this->getFormErrors($form);

        return $errors[0] ?? 'Formulaire invalide.';
    }
}
