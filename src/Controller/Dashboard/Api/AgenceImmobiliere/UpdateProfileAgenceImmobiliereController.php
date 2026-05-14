<?php

namespace App\Controller\Dashboard\Api\AgenceImmobiliere;

use App\Entity\User;
use App\Form\Dashboard\AgenceImmobiliere\ProfileAgenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;

final class UpdateProfileAgenceImmobiliereController extends AbstractController
{
    #[Route('/dashboard/api/profile', name: 'api_profile_agence_immobiliere', methods: ['POST'])]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        CsrfTokenManagerInterface $csrfTokenManager,
        UserPasswordHasherInterface $passwordHasher,
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
                'imageName' => $user->getImageName(),
                'imageSize' => $user->getImageSize(),
            ]);
        }

        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            return $this->json([
                'success' => false,
                'message' => 'JSON invalide.',
            ], 400);
        }

        $csrfToken = new CsrfToken('profile_edit', $data['_token'] ?? '');

        if (!$csrfTokenManager->isTokenValid($csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 419);
        }

        $field = $data['field'] ?? null;
        $value = $data['value'] ?? null;

        if ($field === 'plainPassword') {
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

            if (mb_strlen($password) < 8) {
                return $this->json([
                    'success' => false,
                    'message' => 'Le mot de passe doit contenir au moins 8 caractères.',
                ], 422);
            }

            $user->setPassword(
                $passwordHasher->hashPassword($user, $password)
            );

            $entityManager->flush();

            return $this->json([
                'success' => true,
                'field' => $field,
                'value' => '*****************',
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

            if ($field === 'adresse') {
                $form->submit([
                    'adresse' => $value['adresse'] ?? null,
                    'adresseComplement' => $value['adresseComplement'] ?? null,
                    'codePostal' => $value['codePostal'] ?? null,
                    'ville' => $value['ville'] ?? null,
                    'pays' => $value['pays'] ?? null,
                ], false);
            }

            if ($field === 'adresseContact') {
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
                'field' => $field,
                'value' => $value,
            ]);
        }

        if (!$field || !$form->has($field)) {
            return $this->json([
                'success' => false,
                'message' => 'Champ invalide.',
            ], 400);
        }

        $submitData = [
            $field => $value,
        ];

        if ($field === 'numeroContact' && \array_key_exists('whatsApp', $data)) {
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
            'field' => $field,
            'value' => $value,
            'whatsApp' => $data['whatsApp'] ?? null,
        ]);
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
