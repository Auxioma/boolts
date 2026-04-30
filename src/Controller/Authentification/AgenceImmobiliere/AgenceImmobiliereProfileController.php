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

namespace App\Controller\Authentification\AgenceImmobiliere;

use App\Form\Authentification\CompleteProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereProfileController extends AbstractController
{
    public function __construct(
        private MailerInterface $mailer,
    ) {
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/signup/profile',
            'en' => '/pro/signup/profile',
        ],
        name: 'app_professionnelle_profile'
    )]
    public function completeProfile(Request $request, UserRepository $userRepository, EntityManagerInterface $em, Security $security, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('app_dashboard_agence_immobiliere_dashboard');
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

            $user->setIsVerified(true);
            $em->flush();

            /** Send confirmation email */
            $email = (new TemplatedEmail())
                ->from('support@boolts.com')
                ->to((string) $user->getEmail())
                ->subject('Votre code de connexion Boolts')
                ->htmlTemplate('email/authentification/confirmation_inscription/agence_immobiliere.html.twig')
                ->context([
                    'user' => $user,
                ])
            ;

            $this->mailer->send($email);

            $security->login($user, 'App\Security\VisiteurAuthenticator', 'main');

            $session->remove('auth_user_id');
            $session->remove('auth_step');

            return $this->redirectToRoute('app_dashboard_agence_immobiliere_dashboard');
        }

        return $this->render('authentification/agence_immobiliere/profile.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
