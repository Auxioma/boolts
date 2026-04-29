<?php

namespace App\Controller\Authentification\Visiteurs;

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

final class VisiteurProfileCompleteController extends AbstractController
{
    public function __construct(
        private MailerInterface        $mailer,
    ) {
    }

    #[Route(
        path: [
            'fr' => '/fr/signup/profile',
            'en' => '/signup/profile'
        ],
        name: 'app_visiteur_profile_complete'
    )]
    public function index(Request $request,UserRepository $userRepository, EntityManagerInterface $em, Security $security, UserPasswordHasherInterface $userPasswordHasher,): Response
    {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('app_visiteur_inscription');
        }

        $user = $userRepository->find($authUserId);

        if (!$user) {
            return $this->redirectToRoute('app_visiteur_inscription');
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
                ->htmlTemplate('email/authentification/confirmation_inscription/visiteurs.html.twig')
                ->context([
                    'user' => $user,
                ])
            ;

            $this->mailer->send($email);

            $security->login($user, 'App\Security\VisiteurAuthenticator', 'main');

            $session->remove('auth_user_id');
            $session->remove('auth_step'); 


            return $this->redirectToRoute('app_visiteur_dashboard');
        }

        return $this->render('authentification/visiteurs/complete_profile_utilisateur.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
