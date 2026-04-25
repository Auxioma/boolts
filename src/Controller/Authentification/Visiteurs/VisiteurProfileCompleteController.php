<?php

namespace App\Controller\Authentification\Visiteurs;

use App\Form\Authentification\CompleteProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurProfileCompleteController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/completez-votre-profil-utilisateur',
            'en' => '/user-profile-complete'
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
