<?php

namespace App\Controller\Authentification\Visiteurs;

use App\Form\Authentification\AuthCodeType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class VisiteurCodeRegisterontroller extends AbstractController
{
    #[Route(
        path: [
            'fr' => '/verification-code',
            'en' => '/verify-code'
        ],
        name: 'app_visiteur_verification_code'
    )]
    public function index(Request $request): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        $codeForm = $this->createForm(AuthCodeType::class);
        $codeForm->handleRequest($request);

        /* validation et vérification de l'OTP */
        if ($codeForm->isSubmitted() && $codeForm->isValid()) {
            dd($codeForm->getData());
            if (!$authUserId) {
                $this->addFlash('danger', 'Session expirée. Veuillez recommencer.');

                return $this->redirectToRoute('app_auth');
            }
        }


        return $this->render('authentification/visiteurs/code_verification.html.twig', [
            'codeForm' => $codeForm
        ]);
    }
}
