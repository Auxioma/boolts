<?php

namespace App\Controller\Authentification\AgenceImmobiliere;

use App\Entity\User;
use App\Form\Authentification\AuthCodeType;
use App\Repository\UserRepository;
use App\Service\Authentification\EmailVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereOptController extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/fr/pro/signup/verify',
            'en' => '/pro/signup/verify'
        ],
        name: 'app_professionnelle_otp'
    )]
    public function opt(Request $request, UserRepository $userRepository, EntityManagerInterface $em, EmailVerificationService $emailVerificationService): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        $codeForm = $this->createForm(AuthCodeType::class);
        $codeForm->handleRequest($request);

        if ($codeForm->isSubmitted() && $codeForm->isValid()) {
            if (!$authUserId) {
                $this->addFlash('danger', 'Session expirée. Veuillez recommencer.');

                return $this->redirectToRoute('app_professionnelle_otp');
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

            $user
                ->setIsVerified(true)
                ->clearEmailAuthCode()
            ;

            $em->flush();

            return $this->redirectToRoute('app_professionnelle_profile');
        }

        return $this->render('authentification/agence_immobiliere/otp.html.twig', [
            'codeForm' => $codeForm->createView(),
        ]);
    }
}
