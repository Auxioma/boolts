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

use App\Entity\Property;
use App\Entity\PropertyImage;
use App\Entity\User;
use App\Repository\PropertyImageRepository;
use App\Repository\PropertyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Csrf\CsrfToken;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Gestion AJAX des photos de l'étape 6 du tunnel « Mes biens ».
 *
 * Chaque photo est téléversée, réordonnée ou supprimée individuellement, sans
 * repasser par le formulaire Symfony complet (trop lent avec 30 à 50 images).
 * Le bien courant est celui de la session (`mes_biens_property_id`), comme pour
 * la génération IA de description.
 */
#[Route('/mes/biens/photos', name: 'agence_immobiliere_mes_biens_photo_')]
#[IsGranted('ROLE_AGENCE')]
final class AgenceImmobiliereMesBiensImagesController extends AbstractController
{
    private const CSRF_TOKEN_ID = 'mes_biens_photos';
    private const MAX_IMAGES = 50;
    private const MAX_FILE_SIZE = 15 * 1024 * 1024;
    private const ALLOWED_MIME_PREFIX = 'image/';
    private const DISALLOWED_MIMES = ['image/svg+xml'];

    public function __construct(
        private readonly CsrfTokenManagerInterface $csrfTokenManager,
        private readonly EntityManagerInterface $entityManager,
        private readonly CacheManager $imagineCacheManager,
    ) {
    }

    #[Route('/televerser', name: 'upload', methods: ['POST'])]
    public function upload(
        Request $request,
        PropertyRepository $propertyRepository,
    ): JsonResponse {
        $error = $this->guard(
            $request,
            $request->request->getString('csrfToken'),
            $propertyRepository,
            $property
        );

        if (null !== $error) {
            return $error;
        }

        if ($property->getPropertyImages()->count() >= self::MAX_IMAGES) {
            return $this->error(
                \sprintf('Vous ne pouvez pas dépasser %d photos.', self::MAX_IMAGES),
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $file = $request->files->get('image');

        if (!$file instanceof UploadedFile || !$file->isValid()) {
            return $this->error(
                'Aucun fichier valide reçu.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $mime = (string) $file->getMimeType();

        if (
            !str_starts_with($mime, self::ALLOWED_MIME_PREFIX)
            || \in_array($mime, self::DISALLOWED_MIMES, true)
        ) {
            return $this->error(
                'Le fichier doit être une image (JPG, PNG, WebP…).',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($file->getSize() > self::MAX_FILE_SIZE) {
            return $this->error(
                'Chaque image doit peser moins de 15 Mo.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $image = new PropertyImage();
        $image->setImageFile($file);
        $image->setPosition($this->nextPosition($property));

        $property->addPropertyImage($image);

        $this->entityManager->persist($image);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'image' => $this->serializeImage($image),
        ]);
    }

    #[Route('/reordonner', name: 'reorder', methods: ['POST'])]
    public function reorder(
        Request $request,
        PropertyRepository $propertyRepository,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);

        if (!\is_array($payload)) {
            return $this->error('Données invalides.', Response::HTTP_BAD_REQUEST);
        }

        $error = $this->guard(
            $request,
            (string) ($payload['csrfToken'] ?? ''),
            $propertyRepository,
            $property
        );

        if (null !== $error) {
            return $error;
        }

        $order = array_values(array_filter(array_map(
            'intval',
            (array) ($payload['order'] ?? [])
        )));

        if ([] === $order) {
            return $this->error('Ordre vide.', Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        /** @var array<int, PropertyImage> $imagesById */
        $imagesById = [];

        foreach ($property->getPropertyImages() as $propertyImage) {
            $imagesById[$propertyImage->getId()] = $propertyImage;
        }

        if ([] !== array_diff($order, array_keys($imagesById))) {
            return $this->error(
                'Une des photos ne fait pas partie de ce bien.',
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $positions = [];
        $position = 1;

        foreach ($order as $imageId) {
            $imagesById[$imageId]->setPosition($position);
            $positions[$imageId] = $position;
            ++$position;
        }

        // Photos non citées dans l'ordre reçu (upload concurrent) : placées à la fin.
        foreach ($imagesById as $imageId => $propertyImage) {
            if (!isset($positions[$imageId])) {
                $propertyImage->setPosition($position);
                $positions[$imageId] = $position;
                ++$position;
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'positions' => $positions,
        ]);
    }

    #[Route('/{id}/supprimer', name: 'delete', methods: ['POST'])]
    public function delete(
        int $id,
        Request $request,
        PropertyRepository $propertyRepository,
        PropertyImageRepository $propertyImageRepository,
    ): JsonResponse {
        $payload = json_decode($request->getContent(), true);
        $csrfToken = \is_array($payload)
            ? (string) ($payload['csrfToken'] ?? '')
            : $request->request->getString('csrfToken');

        $error = $this->guard(
            $request,
            $csrfToken,
            $propertyRepository,
            $property
        );

        if (null !== $error) {
            return $error;
        }

        if ($id < 1) {
            return $this->error('Photo introuvable.', Response::HTTP_NOT_FOUND);
        }

        $image = $propertyImageRepository->find($id);

        if (
            !$image instanceof PropertyImage
            || $image->getProperty()?->getId() !== $property->getId()
        ) {
            return $this->error(
                'Photo introuvable.',
                Response::HTTP_NOT_FOUND
            );
        }

        $property->removePropertyImage($image);
        $this->entityManager->remove($image);
        $this->entityManager->flush();

        // Renumérotation 1..n des photos restantes.
        $position = 1;

        foreach ($property->getPropertyImages() as $propertyImage) {
            $propertyImage->setPosition($position);
            ++$position;
        }

        $this->entityManager->flush();

        return $this->json(['success' => true]);
    }

    /**
     * Contrôles communs aux 3 routes. Renvoie une `JsonResponse` d'erreur, ou
     * `null` si tout est valide — auquel cas `$property` est renseigné.
     */
    private function guard(
        Request $request,
        string $csrfToken,
        PropertyRepository $propertyRepository,
        ?Property &$property,
    ): ?JsonResponse {
        $property = null;

        if (!$request->isXmlHttpRequest()) {
            return $this->error('Requête invalide.', Response::HTTP_BAD_REQUEST);
        }

        if (!$this->csrfTokenManager->isTokenValid(new CsrfToken(self::CSRF_TOKEN_ID, $csrfToken))) {
            return $this->error('Token CSRF invalide.', Response::HTTP_FORBIDDEN);
        }

        $user = $this->getUser();

        if (!$user instanceof User) {
            return $this->error('Vous devez être connecté.', Response::HTTP_FORBIDDEN);
        }

        if (!$request->hasSession()) {
            return $this->error('Aucun bien en cours.', Response::HTTP_NOT_FOUND);
        }

        $propertyId = $request->getSession()->get('mes_biens_property_id');

        if (null === $propertyId || '' === $propertyId) {
            return $this->error('Aucun bien en cours.', Response::HTTP_NOT_FOUND);
        }

        $found = $propertyRepository->find((int) $propertyId);

        if (!$found instanceof Property) {
            return $this->error('Aucun bien en cours.', Response::HTTP_NOT_FOUND);
        }

        if ($found->getUser()?->getId() !== $user->getId()) {
            return $this->error(
                'Vous ne pouvez pas modifier ce bien.',
                Response::HTTP_FORBIDDEN
            );
        }

        $property = $found;

        return null;
    }

    private function nextPosition(Property $property): int
    {
        $max = 0;

        foreach ($property->getPropertyImages() as $propertyImage) {
            $max = max($max, (int) $propertyImage->getPosition());
        }

        return $max + 1;
    }

    /**
     * @return array{id: int, position: int, thumbnailUrl: string, fullUrl: string}
     */
    private function serializeImage(PropertyImage $image): array
    {
        $relativePath = 'properties/'.$image->getImageName();

        return [
            'id' => (int) $image->getId(),
            'position' => (int) $image->getPosition(),
            'thumbnailUrl' => $this->imagineCacheManager->getBrowserPath(
                $relativePath,
                'property_upload_thumbnail'
            ),
            'fullUrl' => '/'.$relativePath,
        ];
    }

    private function error(string $message, int $status): JsonResponse
    {
        return $this->json([
            'success' => false,
            'message' => $message,
        ], $status);
    }
}
