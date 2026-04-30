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
use App\Form\Authentification\AuthEmailType;
use App\Repository\UserRepository;
use App\Service\Authentification\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurRegisterController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/signup',
            'en' => '/signup',
        ],
        name: 'app_visiteur_inscription'
    )]
    public function index(Request $request, UserRepository $userRepository, EntityManagerInterface $em, EmailVerificationService $emailVerificationService): Response
    {
        /* si utilisateur deja en session, je redirige vers l'admin visiteur */
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $session = $request->getSession();
        $authStep = $session->get('auth_step', 'email');
        $authUserId = $session->get('auth_user_id');

        /* creation du formulaire */
        $emailForm = $this->createForm(AuthEmailType::class);
        $emailForm->handleRequest($request);

        /* traitement du formulaire */
        if ($emailForm->isSubmitted() && $emailForm->isValid()) {
            $email = mb_strtolower(mb_trim((string) $emailForm->get('email')->getData()));
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user instanceof User) {
                $this->addFlash('warning', 'Un compte existe déjà avec cette adresse. Connectez vous ici');

                return $this->redirectToRoute('app_visiteur_inscription');
            }

            /* si pas de compte, je vais l'enregistrer en session et rediriger vers l'OTP */
            $user = new User();
            $user->setEmail($email);
            $user->setRoles(['ROLE_USER']);
            $em->persist($user);
            $em->flush();

            /* je stocke l'utilisateur en session, et envoyer le code par E-mail */
            $emailVerificationService->prepare($user);
            $emailVerificationService->send($user);

            $session->set('auth_user_id', $user->getId());
            $session->set('auth_step', 'code');

            return $this->redirectToRoute('app_visiteur_verification_code');
        }

        return $this->render('authentification/visiteurs/inscription.html.twig', [
            'emailForm' => $emailForm->createView(),
        ]);
    }
}
