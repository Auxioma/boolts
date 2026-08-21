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
    private const PROFILE_TRANSLATION_LOCALES = ['fr', 'en'];

    private const TRANSLATED_PROFILE_FIELDS = [
        'adresse',
        'adresseComplement',
        'ville',
        'description',
        'adresseContact',
        'villeContact',
        'paysContact',
        'adresseComplementContact',
    ];

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

            return $this->json(array_merge([
                'success' => true,
                'message' => 'Les modifications ont été effectuées avec succès !',
                'imageName' => $user->getImageName(),
                'imageSize' => $user->getImageSize(),
            ], $this->getPublicProfilePayload($user)));
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

            return $this->json(array_merge([
                'success' => true,
                'message' => 'Les modifications ont été effectuées avec succès !',
                'field' => $field,
                'value' => '*****************',
            ], $this->getPublicProfilePayload($user)));
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

            return $this->json(array_merge([
                'success' => true,
                'message' => 'Les horaires ont été enregistrés avec succès !',
                'field' => $field,
                'value' => $value,
            ], $this->getPublicProfilePayload($user)));
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

            return $this->json(array_merge([
                'success' => true,
                'message' => 'Les langues parlées ont été enregistrées avec succès !',
                'field' => $field,
                'value' => $value,
            ], $this->getPublicProfilePayload($user)));
        }

        $form = $this->createForm(ProfileAgenceType::class, $user);

        if (\in_array($field, ['adresse', 'adresseContact'], true)) {
            if (!\is_array($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Adresse invalide.',
                ], 422);
            }

            $submitData = [];

            if ('adresse' === $field) {
                $submitData = [
                    'adresse' => $value['adresse'] ?? null,
                    'adresseComplement' => $value['adresseComplement'] ?? null,
                    'codePostal' => $value['codePostal'] ?? null,
                    'ville' => $value['ville'] ?? null,
                    'pays' => $value['pays'] ?? null,
                ];

                $form->submit($submitData, false);
            }

            if ('adresseContact' === $field) {
                $submitData = [
                    'adresseContact' => $value['adresseContact'] ?? null,
                    'codePostalContact' => $value['codePostalContact'] ?? null,
                    'villeContact' => $value['villeContact'] ?? null,
                    'paysContact' => $value['paysContact'] ?? null,
                ];

                $form->submit($submitData, false);
            }

            if (!$form->isValid()) {
                return $this->json([
                    'success' => false,
                    'message' => $this->getFirstFormError($form),
                    'errors' => $this->getFormErrors($form),
                ], 422);
            }

            $this->syncTranslatedProfileFields($user, $submitData);

            $entityManager->flush();

            return $this->json(array_merge([
                'success' => true,
                'message' => 'Les modifications ont été effectuées avec succès !',
                'field' => $field,
                'value' => $value,
            ], $this->getPublicProfilePayload($user)));
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

        if ($this->isTranslatedProfileField($field)) {
            $this->syncTranslatedProfileFields($user, [$field => $value]);
        }

        $entityManager->flush();

        return $this->json(array_merge([
            'success' => true,
            'message' => 'Les modifications ont été effectuées avec succès !',
            'field' => $field,
            'value' => $value,
            'whatsApp' => $data['whatsApp'] ?? null,
        ], $this->getPublicProfilePayload($user)));
    }

    private function getPublicProfilePayload(User $user): array
    {
        $slug = $user->getSlug();

        return [
            'publicProfileUrl' => $this->hasFilledValue($slug)
                ? $this->generateUrl('app_public_detail_agence', ['slug' => $slug])
                : null,
        ];
    }

    private function hasFilledValue(?string $value): bool
    {
        return '' !== mb_trim($value ?? '');
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

    private function isTranslatedProfileField(string $field): bool
    {
        return \in_array($field, self::TRANSLATED_PROFILE_FIELDS, true);
    }

    private function syncTranslatedProfileFields(User $user, array $fields): void
    {
        foreach (self::PROFILE_TRANSLATION_LOCALES as $locale) {
            $translation = $user->translate($locale, false);

            foreach ($fields as $field => $value) {
                if (!$this->isTranslatedProfileField($field)) {
                    continue;
                }

                $setter = 'set'.ucfirst($field);

                if (!method_exists($translation, $setter)) {
                    continue;
                }

                $translation->{$setter}($this->normalizeTranslatedValue($value));
            }
        }

        $user->mergeNewTranslations();
    }

    private function normalizeTranslatedValue(mixed $value): ?string
    {
        if (null === $value) {
            return null;
        }

        if (\is_scalar($value) || $value instanceof \Stringable) {
            return (string) $value;
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
