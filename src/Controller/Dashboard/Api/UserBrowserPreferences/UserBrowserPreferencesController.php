<?php

namespace App\Controller\Dashboard\Api\UserBrowserPreferences;

use App\Entity\FuseauHoraire;
use App\Entity\Langues;
use App\Entity\Pays;
use App\Entity\User;
use App\Repository\FuseauHoraireRepository;
use App\Repository\LanguesRepository;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED_FULLY')]
final class UserBrowserPreferencesController extends AbstractController
{
    #[Route('/api/user/browser-preferences', name: 'app_user_browser_preferences', methods: ['POST'])]
    public function save(
        Request $request,
        EntityManagerInterface $entityManager,
        PaysRepository $paysRepository,
        LanguesRepository $languesRepository,
        FuseauHoraireRepository $fuseauHoraireRepository,
    ): JsonResponse {
        /** @var User $user */
        $user = $this->getUser();

        $data = json_decode($request->getContent(), true);

        if (!is_array($data)) {
            return $this->json([
                'success' => false,
                'message' => 'Données invalides.',
            ], 400);
        }

        $csrfToken = $data['_token'] ?? null;

        if (!$this->isCsrfTokenValid('browser_preferences', $csrfToken)) {
            return $this->json([
                'success' => false,
                'message' => 'Token CSRF invalide.',
            ], 403);
        }

        $countryIso = strtoupper((string) ($data['country'] ?? ''));
        $languageIso = strtolower((string) ($data['language'] ?? ''));
        $timeZoneName = (string) ($data['timeZone'] ?? '');

        /*
         * Exemple :
         * country = FR
         * language = fr
         * timeZone = Europe/Paris
         */

        if ($countryIso !== '') {
            $pays = $paysRepository->findOneBy([
                'iso' => $countryIso,
            ]);

            if ($pays instanceof Pays && method_exists($user, 'setPays')) {
                $user->setPays($pays);
                $user->setDevise($pays->getDevise());
            }
        }

        if ($languageIso !== '') {
            $langue = $languesRepository->findOneBy([
                'iso' => $languageIso,
            ]);

            if ($langue instanceof Langues && method_exists($user, 'setLangues')) {
                $user->setLangues($langue);
            }
        }

        if ($timeZoneName !== '') {
            $fuseauHoraire = $fuseauHoraireRepository->findOneBy([
                'nom' => $timeZoneName,
            ]);

            if (!$fuseauHoraire instanceof FuseauHoraire) {
                $fuseauHoraire = new FuseauHoraire();
                $fuseauHoraire->setNom($timeZoneName);
                $entityManager->persist($fuseauHoraire);
            }

            if (method_exists($user, 'setFuseauHoraire')) {
                $user->setFuseauHoraire($fuseauHoraire);
            }
        }

        $entityManager->flush();

        return $this->json([
            'success' => true,
        ]);
    }
}
