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
    private const GOOGLE_REGISTER_TYPE_SESSION_KEY = 'google_register_type';
    private const GOOGLE_PROFESSIONAL_FIRST_REGISTRATION_SESSION_KEY = 'google_professional_first_registration';
    private const AUTH_USER_ID_SESSION_KEY = 'auth_user_id';
    private const AUTH_STEP_SESSION_KEY = 'auth_step';

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
        $session = $request->getSession();
        $session->remove(self::GOOGLE_PROFESSIONAL_FIRST_REGISTRATION_SESSION_KEY);

        $requestedRole = 'professionnel' === $session->get(self::GOOGLE_REGISTER_TYPE_SESSION_KEY)
            ? 'ROLE_AGENCE'
            : 'ROLE_USER';

        return new SelfValidatingPassport(
            new UserBadge($accessToken->getToken(), function () use ($client, $accessToken, $requestedRole, $session) {
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

                    if ('ROLE_AGENCE' === $requestedRole) {
                        $session->set(self::AUTH_USER_ID_SESSION_KEY, $user->getId());
                        $session->set(self::AUTH_STEP_SESSION_KEY, 'step4');
                        $session->set(self::GOOGLE_PROFESSIONAL_FIRST_REGISTRATION_SESSION_KEY, true);
                    }
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
    ): ?Response {
        $session = $request->getSession();
        $completeProfessionalRegistration = (bool) $session->get(
            self::GOOGLE_PROFESSIONAL_FIRST_REGISTRATION_SESSION_KEY,
            false
        );

        $session->remove(self::GOOGLE_REGISTER_TYPE_SESSION_KEY);
        $session->remove(self::GOOGLE_PROFESSIONAL_FIRST_REGISTRATION_SESSION_KEY);

        $user = $token->getUser();
        $roles = $user instanceof User ? $user->getRoles() : [];

        if (\in_array('ROLE_ADMIN', $roles, true)) {
            return new RedirectResponse($this->urlGen->generate('admin'));
        }

        if (\in_array('ROLE_AGENCE', $roles, true)) {
            if ($completeProfessionalRegistration && $user instanceof User) {
                $session->set(self::AUTH_USER_ID_SESSION_KEY, $user->getId());
                $session->set(self::AUTH_STEP_SESSION_KEY, 'step4');

                return new RedirectResponse($this->urlGen->generate('app_professionnelle_step_quatre'));
            }

            return new RedirectResponse($this->urlGen->generate('agence_immobiliere_dashboard'));
        }

        return new RedirectResponse($this->urlGen->generate('app_visiteur_dashboard'));
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $session = $request->getSession();
        $route = 'professionnel' === $session->get(self::GOOGLE_REGISTER_TYPE_SESSION_KEY)
            ? 'app_professionnelle_connexion'
            : 'app_login';

        $session->remove(self::GOOGLE_REGISTER_TYPE_SESSION_KEY);
        $session->remove(self::GOOGLE_PROFESSIONAL_FIRST_REGISTRATION_SESSION_KEY);

        return new RedirectResponse($this->urlGen->generate($route));
    }
}
