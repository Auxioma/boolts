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

namespace App\Controller\Authentification\AgenceImmobiliere;

use App\Entity\User;
use App\Form\Authentification\AuthCodeType;
use App\Repository\UserRepository;
use App\Service\Authentification\AgencyRegistrationProgress;
use App\Service\Authentification\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module Authentification / AgenceImmobiliere / AgenceImmobiliereOptController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereOptController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/pro/signup/verify',
            'en' => '/pro/signup/verify',
        ],
        name: 'app_professionnelle_otp'
    )]
    /**
     * Handles the opt controller action.
     */
    public function opt(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        EmailVerificationService $emailVerificationService,
        AgencyRegistrationProgress $agencyRegistrationProgress,
    ): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            $this->addFlash('danger', 'Session expirée. Veuillez recommencer.');

            return $this->redirectToRoute('app_professionnelle_register');
        }

        $user = $userRepository->find($authUserId);

        if (!$user instanceof User) {
            $session->remove('auth_user_id');
            $session->remove('auth_step');

            $this->addFlash('danger', 'Utilisateur introuvable.');

            return $this->redirectToRoute('app_professionnelle_register');
        }

        if (!$agencyRegistrationProgress->isIncompleteAgencyRegistration($user)) {
            $session->remove('auth_user_id');
            $session->remove('auth_step');

            return $this->redirectToRoute('app_professionnelle_connexion');
        }

        if (
            AgencyRegistrationProgress::STEP_CODE !== $session->get('auth_step')
            && !$user->getEmailAuthCode()
        ) {
            return $this->redirectToRoute($agencyRegistrationProgress->routeForCurrentStep($user));
        }

        $codeForm = $this->createForm(AuthCodeType::class);
        $codeForm->handleRequest($request);

        if ($codeForm->isSubmitted() && $codeForm->isValid()) {
            if ($user->getFailedVerificationAttempts() >= 5) {
                $this->addFlash('danger', 'Trop de tentatives. Veuillez demander un nouveau code.');

                return $this->redirectToRoute('app_professionnelle_otp');
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

                return $this->redirectToRoute('app_professionnelle_otp');
            }

            $currentStep = $agencyRegistrationProgress->currentStep($user);

            if (AgencyRegistrationProgress::STEP_CODE === $currentStep) {
                $currentStep = AgencyRegistrationProgress::STEP_PROFILE;
            }

            $user
                ->setAgencyRegistrationStep($currentStep)
                ->setIsVerified(AgencyRegistrationProgress::STEP_PROFILE === $currentStep && !$user->getPassword())
                ->clearEmailAuthCode()
            ;

            $session->set('auth_step', $currentStep);

            $em->flush();

            return $this->redirectToRoute($agencyRegistrationProgress->routeForStep($currentStep));
        }

        return $this->render('authentification/agence_immobiliere/otp.html.twig', [
            'codeForm' => $codeForm->createView(),
        ]);
    }
}
