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

namespace App\Controller\Authentification\Visiteurs;

use App\Entity\User;
use App\Form\Authentification\AuthCodeType;
use App\Repository\UserRepository;
use App\Service\Authentification\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurCodeRegisterController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/signup/verify',
            'en' => '/signup/verify',
        ],
        name: 'app_visiteur_verification_code'
    )]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $em, EmailVerificationService $emailVerificationService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        $codeForm = $this->createForm(AuthCodeType::class);
        $codeForm->handleRequest($request);

        if ($codeForm->isSubmitted() && $codeForm->isValid()) {
            if (!$authUserId) {
                $this->addFlash('danger', 'Session expirée. Veuillez recommencer.');

                return $this->redirectToRoute('app_visiteur_verification_code');
            }

            $user = $userRepository->find($authUserId);

            if (!$user instanceof User) {
                $session->remove('auth_user_id');
                $session->remove('auth_step');

                $this->addFlash('danger', 'Utilisateur introuvable.');

                return $this->redirectToRoute('app_auth');
            }

            if ($user->getFailedVerificationAttempts() >= 5) {
                $this->addFlash('danger', 'Trop de tentatives. Veuillez demander un nouveau code.');

                return $this->redirectToRoute('app_visiteur_verification_code');
            }

            $submittedCode = mb_trim((string) $codeForm->get('code')->getData());

            if (!$emailVerificationService->verify($user, $submittedCode)) {
                $user->incrementFailedVerificationAttempts();
                $em->flush();

                if (
                    null !== $user->getEmailAuthCodeExpiresAt()
                    && $user->getEmailAuthCodeExpiresAt() < new \DateTimeImmutable()
                ) {
                    $this->addFlash('danger', 'Le code a expiré. Demandez un nouveau code.');
                } else {
                    $this->addFlash('danger', 'Le code saisi est invalide.');
                }

                return $this->redirectToRoute('app_visiteur_verification_code');
            }

            $user
                ->setIsVerified(true)
                ->clearEmailAuthCode()
            ;

            $em->flush();

            return $this->redirectToRoute('app_visiteur_profile_complete');
        }

        return $this->render('authentification/visiteurs/code_verification.html.twig', [
            'codeForm' => $codeForm->createView(),
        ]);
    }
}
