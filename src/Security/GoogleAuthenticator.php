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

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GoogleAuthenticator extends OAuth2Authenticator
{
    public function __construct(
        private ClientRegistry $clientRegistry,
        private EntityManagerInterface $em,
        private UrlGeneratorInterface $urlGen,
    ) {
    }

    public function supports(Request $request): bool
    {
        return str_starts_with($request->getPathInfo(), '/connect/google/check');
    }

    public function authenticate(Request $request): Passport
    {
        $client = $this->clientRegistry->getClient('google');
        $accessToken = $this->fetchAccessToken($client);

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($client, $accessToken) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();
                $googleId = $googleUser->getId();

                // 1. Cherche d'abord par Google ID (prioritaire)
                $user = $this->em->getRepository(User::class)
                    ->findOneBy(['googleId' => $googleId]);

                // 2. fallback email
                if (!$user) {
                    $user = $this->em->getRepository(User::class)
                        ->findOneBy(['email' => $email]);
                }

                // 3. création user si inexistant
                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setGoogleId($googleId);
                    $user->setIsVerified(true);
                    $user->setRoles(['ROLE_USER']);
                    $user->setFirstName($googleUser->getFirstName());
                    $user->setLastName($googleUser->getLastName());

                    $this->em->persist($user);
                    $this->em->flush();
                } else {
                    // mise à jour googleId si login email existant
                    if (!$user->getGoogleId()) {
                        $user->setGoogleId($googleId);
                        $user->setIsVerified(true);
                        $this->em->flush();
                    }
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(Request $request, $token, string $firewallName): ?RedirectResponse
    {
        return new RedirectResponse($this->urlGen->generate('app_admin_users_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?RedirectResponse
    {
        return new RedirectResponse($this->urlGen->generate('app_auth'));
    }
}
