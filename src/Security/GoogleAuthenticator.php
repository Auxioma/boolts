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

namespace App\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use KnpU\OAuth2ClientBundle\Security\Authenticator\OAuth2Authenticator;
use League\OAuth2\Client\Provider\GoogleUser;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
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
        $requestedRole = 'professionnel' === $request->getSession()->get('google_register_type')
            ? 'ROLE_AGENCE'
            : 'ROLE_USER';

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($client, $accessToken, $requestedRole) {
                /** @var GoogleUser $googleUser */
                $googleUser = $client->fetchUserFromToken($accessToken);

                $email = $googleUser->getEmail();
                $user = $this->em->getRepository(User::class)
                    ->findOneBy(['email' => $email]);

                if (!$user) {
                    $user = new User();
                    $user->setEmail($email);
                    $user->setIsVerified(true);
                    $user->setRoles([$requestedRole]);
                    $user->setPrenom($googleUser->getFirstName());
                    $user->setNom($googleUser->getLastName());

                    $this->em->persist($user);
                    $this->em->flush();
                } else {
                    $changed = false;

                    if (!$user->isVerified()) {
                        $user->setIsVerified(true);
                        $changed = true;
                    }

                    if ('ROLE_AGENCE' === $requestedRole) {
                        $roles = $user->getRoles();

                        if (
                            !\in_array('ROLE_ADMIN', $roles, true)
                            && !\in_array('ROLE_AGENCE', $roles, true)
                        ) {
                            $roles = array_values(array_filter(
                                $roles,
                                static fn (string $role): bool => 'ROLE_USER' !== $role
                            ));
                            $roles[] = 'ROLE_AGENCE';

                            $user->setRoles(array_values(array_unique($roles)));
                            $changed = true;
                        }
                    }

                    if ($changed) {
                        $this->em->flush();
                    }
                }

                return $user;
            })
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): ?Response
    {
        $request->getSession()->remove('google_register_type');

        $user = $token->getUser();
        $roles = $user instanceof User ? $user->getRoles() : [];

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGen->generate('admin'));
        }

        if (\in_array('ROLE_AGENCE', $roles, true)) {
            return new RedirectResponse($this->urlGen->generate('agence_immobiliere_dashboard'));
        }

        return new RedirectResponse($this->urlGen->generate('app_visiteur_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $route = 'professionnel' === $request->getSession()->get('google_register_type')
            ? 'app_professionnelle_connexion'
            : 'app_login';

        $request->getSession()->remove('google_register_type');

        return new RedirectResponse($this->urlGen->generate($route));
    }
}
