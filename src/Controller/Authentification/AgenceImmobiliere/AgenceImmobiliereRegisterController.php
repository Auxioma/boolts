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
use App\Form\Authentification\AuthEmailType;
use App\Repository\UserRepository;
use App\Service\Authentification\AgencyRegistrationProgress;
use App\Service\Authentification\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module Authentification / AgenceImmobiliere / AgenceImmobiliereRegisterController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class AgenceImmobiliereRegisterController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/pro/signup',
            'en' => '/pro/signup',
        ],
        name: 'app_professionnelle_register'
    )]
    /**
     * Handles the registerPro controller action.
     */
    public function registerPro(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        EmailVerificationService $emailVerificationService,
        AgencyRegistrationProgress $agencyRegistrationProgress,
    ): Response
    {
        /* si utilisateur deja en session, je redirige vers l'admin visiteur */
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if ($authUserId) {
            $authUser = $userRepository->find($authUserId);

            if ($authUser instanceof User && $agencyRegistrationProgress->isIncompleteAgencyRegistration($authUser)) {
                return $this->redirectToRoute($agencyRegistrationProgress->routeForCurrentStep($authUser));
            }

            $session->remove('auth_user_id');
            $session->remove('auth_step');
        }

        /* creation du formulaire */
        $emailForm = $this->createForm(AuthEmailType::class);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $email = mb_strtolower(mb_trim((string) $emailForm->get('email')->getData()));
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                if ($agencyRegistrationProgress->isIncompleteAgencyRegistration($user)) {
                    $step = $agencyRegistrationProgress->currentStep($user);
                    $user->setAgencyRegistrationStep($step);

                    $emailVerificationService->prepare($user);
                    $emailVerificationService->send($user);

                    $session->set('auth_user_id', $user->getId());
                    $session->set('auth_step', AgencyRegistrationProgress::STEP_CODE);

                    $this->addFlash('success', 'Un code de vérification vient de vous être envoyé pour reprendre votre inscription.');

                    return $this->redirectToRoute('app_professionnelle_otp');
                }

                $this->addFlash('warning', 'Un compte existe déjà avec cette adresse. Connectez-vous ici.');

                return $this->redirectToRoute('app_professionnelle_connexion');
            }

            /* si pas de compte, je vais l'enregistrer en session et rediriger vers l'OTP */
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_AGENCE']);
            $user->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_CODE);
            $em->persist($user);
            $em->flush();

            /* je stocke l'utilisateur en session, et envoyer le code par E-mail */
            $emailVerificationService->prepare($user);
            $emailVerificationService->send($user);

            $session->set('auth_user_id', $user->getId());
            $session->set('auth_step', AgencyRegistrationProgress::STEP_CODE);

            return $this->redirectToRoute('app_professionnelle_otp');
        }

        return $this->render('authentification/agence_immobiliere/register.html.twig', [
            'emailForm' => $emailForm->createView(),
        ]);
    }
}
