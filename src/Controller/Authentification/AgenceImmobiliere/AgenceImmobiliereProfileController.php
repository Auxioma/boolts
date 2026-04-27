<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use App\Form\Authentification\CompleteProfileType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereProfileController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/register-professionnelle-profile',
            'en' => '/professional-login'
        ],
        name: 'app_professionnelle_profile'
    )]
    public function completeProfile(Request $request,UserRepository $userRepository, EntityManagerInterface $em, Security $security, UserPasswordHasherInterface $userPasswordHasher): Response
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
