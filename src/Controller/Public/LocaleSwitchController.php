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

namespace App\Controller\Public;

use App\Entity\User;
use App\Repository\LanguesRepository;
use App\Service\Intl\VisitorLocaleResolver;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Changement manuel de langue (sélecteur du header et bandeau de la homepage).
 * Le choix est conservé dans un cookie et, pour un utilisateur connecté, dans ses
 * préférences : il n'est plus jamais modifié automatiquement ensuite.
 */
final class LocaleSwitchController extends AbstractController
{
    private const COOKIE_LIFETIME = '+1 year';

    #[Route(
        path: '/locale/{target}',
        name: 'app_locale_switch',
        requirements: ['target' => 'fr|en'],
        methods: ['GET']
    )]
    public function switch(
        Request $request,
        string $target,
        LanguesRepository $languesRepository,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $this->getUser();

        if ($user instanceof User && $user->getLangues()?->getIso() !== $target) {
            $langue = $languesRepository->findOneBy(['iso' => $target]);

            if (null !== $langue) {
                $user->setLangues($langue);
                $entityManager->flush();
            }
        }

        $response = new RedirectResponse($this->safeRedirect($request, $target));
        $response->headers->setCookie($this->cookie(VisitorLocaleResolver::MANUAL_COOKIE, $target, $request));
        $response->headers->setCookie($this->cookie(VisitorLocaleResolver::AUTO_COOKIE, $target, $request));

        return $response;
    }

    /**
     * Uniquement un chemin interne (pas de redirection ouverte vers un autre domaine).
     */
    private function safeRedirect(Request $request, string $target): string
    {
        $redirect = (string) $request->query->get('redirect', '');

        if (1 === preg_match('#^/(?![/\\\\])#', $redirect)) {
            return $redirect;
        }

        return $this->generateUrl('app_home', ['_locale' => $target]);
    }

    private function cookie(string $name, string $value, Request $request): Cookie
    {
        return Cookie::create($name, $value)
            ->withExpires(new \DateTimeImmutable(self::COOKIE_LIFETIME))
            ->withSecure($request->isSecure())
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);
    }
}
