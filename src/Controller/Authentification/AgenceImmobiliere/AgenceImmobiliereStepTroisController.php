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

use App\Entity\HoraireOuverture;
use App\Entity\User;
use App\Form\Authentification\CompleteProfileType;
use App\Form\Authentification\StepCinqType;
use App\Form\Authentification\StepQuatreType;
use App\Form\Authentification\StepSixType;
use App\Repository\UserRepository;
use App\Security\AgenceImmobiliereAuthenticator;
use App\Service\Billing\FreeAgencySubscriptionActivator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereStepTroisController extends AbstractController
{
    private const OPENING_DAYS = [
        'lundi',
        'mardi',
        'mercredi',
        'jeudi',
        'vendredi',
        'samedi',
        'dimanche',
    ];

    #[Route(
        path: [
            'fr' => '/fr/pro/step3',
            'en' => '/pro/step3',
        ],
        name: 'app_professionnelle_step_trois'
    )]
    public function step3(Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        Security $security,
        UserPasswordHasherInterface $userPasswordHasher,
        FreeAgencySubscriptionActivator $freeAgencySubscriptionActivator, ): Response
    {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        $user = $userRepository->find($authUserId);

        if (!$user) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $form = $this->createForm(CompleteProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));

            $user->setIsVerified(false);
            $freeAgencySubscriptionActivator->activate($user);
            $em->flush();

            return $this->redirectToRoute('app_professionnelle_step_quatre');
        }

        return $this->render('authentification/agence_immobiliere/step3.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/step4',
            'en' => '/pro/step4',
        ],
        name: 'app_professionnelle_step_quatre'
    )]
    public function step4(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
    ): Response
    {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        $user = $userRepository->find($authUserId);

        if (!$user) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $form = $this->createForm(StepQuatreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->mergeNewTranslations();
            $em->flush();

            return $this->redirectToRoute('app_professionnelle_step_cinq');
        }

        return $this->render('authentification/agence_immobiliere/step4.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/step5',
            'en' => '/pro/step5',
        ],
        name: 'app_professionnelle_step_cinq'
    )]
    public function step5(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
    ): Response
    {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        $user = $userRepository->find($authUserId);

        if (!$user) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $form = $this->createForm(StepCinqType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->mergeNewTranslations();
            $em->flush();

            return $this->redirectToRoute('app_professionnelle_step_six');
        }

        return $this->render('authentification/agence_immobiliere/step5.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/step6',
            'en' => '/pro/step6',
        ],
        name: 'app_professionnelle_step_six'
    )]
    public function step6(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        Security $security,
    ): Response
    {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        $user = $userRepository->find($authUserId);

        if (!$user) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $this->ensureOpeningHours($user, $em);

        $form = $this->createForm(StepSixType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($user->getHoraireOuvertures() as $horaireOuverture) {
                $em->persist($horaireOuverture);

                if (!$horaireOuverture->isOpen()) {
                    $horaireOuverture
                        ->setOuvertureMatin(null)
                        ->setFermetureMatin(null)
                        ->setOuvertureApresMidi(null)
                        ->setFermetureApresMidi(null)
                    ;
                }
            }

            $user->setIsVerified(true);
            $em->flush();

            $security->login($user, AgenceImmobiliereAuthenticator::class, 'main');

            $session->remove('auth_user_id');
            $session->remove('auth_step');

            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        return $this->render('authentification/agence_immobiliere/step6.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    private function ensureOpeningHours(User $user, EntityManagerInterface $em): void
    {
        $existingDays = [];

        foreach ($user->getHoraireOuvertures() as $horaireOuverture) {
            if (!$horaireOuverture->getJour()) {
                continue;
            }

            $existingDays[$horaireOuverture->getJour()] = true;
        }

        foreach (self::OPENING_DAYS as $day) {
            if (isset($existingDays[$day])) {
                continue;
            }

            $horaireOuverture = (new HoraireOuverture())
                ->setJour($day)
            ;

            $user->addHoraireOuverture($horaireOuverture);
            $em->persist($horaireOuverture);
        }
    }
}
