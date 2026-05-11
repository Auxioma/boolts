<?php

namespace App\Controller\Dashboard\Api\AgenceImmobiliere;

use App\Entity\User;
use App\Form\Dashboard\AgenceImmobiliere\ProfileAgenceType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
    ): JsonResponse {
        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->json([
                'success' => false,
                'message' => 'Utilisateur non connecté.',
            ], 401);
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

        if ($field === 'adresse') {
            if (!\is_array($value)) {
                return $this->json([
                    'success' => false,
                    'message' => 'Adresse invalide.',
                ], 422);
            }

            $form = $this->createForm(ProfileAgenceType::class, $user);

            $form->submit([
                'adresse' => $value['adresse'] ?? null,
                'adresseComplement' => $value['adresseComplement'] ?? null,
                'codePostal' => $value['codePostal'] ?? null,
                'ville' => $value['ville'] ?? null,
                'pays' => $value['pays'] ?? null,
            ], false);

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

        $form = $this->createForm(ProfileAgenceType::class, $user);

        if (!$form->has($field)) {
            return $this->json([
                'success' => false,
                'message' => 'Champ invalide.',
            ], 400);
        }

        $form->submit([
            $field => $value,
        ], false);

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

    private function getFormErrors($form): array
    {
        $errors = [];

        foreach ($form->getErrors(true) as $error) {
            $errors[] = $error->getMessage();
        }

        return $errors;
    }

    private function getFirstFormError($form): string
    {
        $errors = $this->getFormErrors($form);

        return $errors[0] ?? 'Formulaire invalide.';
    }
}
