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
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

/**
 * Authentificateur du back-office (EasyAdmin).
 *
 * Traite le formulaire rendu par « @EasyAdmin/page/login.html.twig » sur la
 * route "admin_login" (POST /admin/login). Seuls les comptes disposant de
 * ROLE_ADMIN — et pas de ROLE_AGENCE — peuvent se connecter ici.
 */
class AdminAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'admin_login';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private UserRepository $userRepository,
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email, function (string $userIdentifier): User {
                $user = $this->userRepository->findOneBy(['email' => $userIdentifier]);

                if (!$user instanceof User || !$this->isAdmin($user)) {
                    /*
                     * Message volontairement neutre : on ne révèle pas si le
                     * compte existe ni s'il possède le rôle administrateur.
                     */
                    throw new CustomUserMessageAuthenticationException(
                        'Identifiants invalides ou accès non autorisé.'
                    );
                }

                return $user;
            }),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );
    }

    public function onAuthenticationSuccess(
        Request $request,
        TokenInterface $token,
        string $firewallName,
    ): ?Response {
        /*
         * Depuis la page de connexion du back-office, on redirige toujours
         * vers le tableau de bord EasyAdmin, sans tenir compte d'une URL
         * cible éventuellement mémorisée par le firewall.
         */
        $this->removeTargetPath($request->getSession(), $firewallName);

        return new RedirectResponse(
            $this->urlGenerator->generate('admin')
        );
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }

    private function isAdmin(User $user): bool
    {
        $roles = $user->getRoles();

        return \in_array('ROLE_ADMIN', $roles, true)
            && !\in_array('ROLE_AGENCE', $roles, true);
    }
}
