<?php

namespace App\Controller\Authentification;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GoogleController extends AbstractController
{
    #[Route('/connect/google/{type}', name: 'app_google_connect')]
    public function connect(string $type, Request $request, ClientRegistry $clientRegistry): RedirectResponse
    {
        if (!in_array($type, ['particulier', 'professionnel'], true)) {
            throw $this->createNotFoundException();
        }
        $request->getSession()->set('google_register_type', $type);

        $client = $clientRegistry->getClient('google');
        $response = $client->redirect(
            ['openid', 'email', 'profile'],
            []
        );

        return $response;
    }

    #[Route('/connect/google/check', name: 'connect_google_check')]
    public function check(): Response
    {
        // Cette méthode est "interceptée" par le système d'auth (authenticator).
        // Si tu arrives ici, c'est que ton firewall/check_path n'est pas configuré correctement.
        throw new \LogicException('This route should be handled by the security authenticator.');
    }
}
