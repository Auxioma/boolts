<?php

namespace App\Controller\Dashboard\Visiteur;

use App\Entity\Favoris;
use App\Entity\Property;
use App\Repository\FavorisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
class FavorisController extends AbstractController
{
    #[Route('/favoris/property/{id}/toggle', name: 'app_favoris_property_toggle', methods: ['POST'])]
    public function toggle(
        Property $property,
        Request $request,
        FavorisRepository $favorisRepository,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        if ($this->isGranted('ROLE_AGENCE')) {
            return $this->json([
                'success' => false,
                'message' => 'Une agence ne peut pas ajouter un bien en favoris.',
            ], 403);
        }

        $token = $request->headers->get('X-CSRF-TOKEN');

        if (!$this->isCsrfTokenValid('favorite_property_' . $property->getId(), $token)) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 400);
        }

        $user = $this->getUser();

        $favori = $favorisRepository->findOneBy([
            'user' => $user,
            'property' => $property,
        ]);

        if ($favori) {
            $entityManager->remove($favori);
            $favorited = false;
        } else {
            $favori = new Favoris();
            $favori->setUser($user);
            $favori->setProperty($property);

            $entityManager->persist($favori);
            $favorited = true;
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
            'favorited' => $favorited,
        ]);
    }
}