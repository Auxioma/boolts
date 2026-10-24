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

namespace App\Controller\Authentification;

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
 * HTTP controller for the first step of the registration flow.
 */
final class RegistrationController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/signup',
            'en' => '/signup',
        ],
        name: 'app_visiteur_inscription',
        defaults: ['registrationType' => 'particulier']
    )]
    #[Route(
        path: [
            'fr' => '/fr/pro/signup',
            'en' => '/pro/signup',
        ],
        name: 'app_professionnelle_register',
        defaults: ['registrationType' => 'professionnel']
    )]
    public function index(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        EmailVerificationService $emailVerificationService,
        AgencyRegistrationProgress $agencyRegistrationProgress,
        string $registrationType,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $isProfessional = 'professionnel' === $registrationType;
        $registrationRoute = $isProfessional
            ? 'app_professionnelle_register'
            : 'app_visiteur_inscription';

        if ($isProfessional) {
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
        }

        $emailForm = $this->createForm(AuthEmailType::class);
        $emailForm->handleRequest($request);

        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $email = mb_strtolower(mb_trim((string) $emailForm->get('email')->getData()));
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                if ($isProfessional && $agencyRegistrationProgress->isIncompleteAgencyRegistration($user)) {
                    $step = $agencyRegistrationProgress->currentStep($user);
                    $user->setAgencyRegistrationStep($step);

                    $emailVerificationService->prepare($user);
                    $emailVerificationService->send($user);

                    $request->getSession()->set('auth_user_id', $user->getId());
                    $request->getSession()->set('auth_step', AgencyRegistrationProgress::STEP_CODE);

                    $this->addFlash('success', 'Un code de vérification vient de vous être envoyé pour reprendre votre inscription.');

                    return $this->redirectToRoute('app_professionnelle_otp');
                }

                $this->addFlash('warning', 'Un compte existe déjà avec cette adresse. Connectez-vous ici.');

                return $this->redirectToRoute(
                    $isProfessional ? 'app_professionnelle_connexion' : $registrationRoute
                );
            }

            $user = new User();
            $user->setEmail($email);
            $user->setRoles([$isProfessional ? 'ROLE_AGENCE' : 'ROLE_USER']);

            if ($isProfessional) {
                $user->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_CODE);
            }

            $em->persist($user);
            $em->flush();

            $emailVerificationService->prepare($user);
            $emailVerificationService->send($user);

            $request->getSession()->set('auth_user_id', $user->getId());
            $request->getSession()->set(
                'auth_step',
                $isProfessional ? AgencyRegistrationProgress::STEP_CODE : 'code'
            );

            return $this->redirectToRoute(
                $isProfessional ? 'app_professionnelle_otp' : 'app_visiteur_verification_code'
            );
        }

        return $this->render('authentification/registration.html.twig', [
            'emailForm' => $emailForm->createView(),
            'registrationType' => $registrationType,
        ]);
    }
}
