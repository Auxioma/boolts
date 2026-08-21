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

namespace App\Service;

use App\Entity\Property;
use App\Entity\PropertyView;
use App\Entity\User;
use App\Repository\PropertyViewRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Uid\Uuid;

final class PropertyViewTracker
{
    /**
     * Cookie permettant d'identifier anonymement un navigateur.
     */
    private const COOKIE_NAME = 'boolts_visitor_id';

    /**
     * Durée du cookie : 1 an.
     */
    private const COOKIE_LIFETIME = '+1 year';

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyViewRepository $propertyViewRepository,
        private readonly RequestStack $requestStack,
        private readonly string $kernelSecret,
    ) {
    }

    /**
     * Enregistre une vue unique.
     *
     * Règles :
     *
     * - uniquement les requêtes GET ;
     * - les robots ne sont pas comptés ;
     * - le propriétaire du bien n'est jamais compté ;
     * - un utilisateur connecté = identifié par son ID ;
     * - un visiteur anonyme = identifié par un cookie UUID ;
     * - 1 vue maximum par bien / visiteur / jour ;
     * - la contrainte UNIQUE en BDD protège également
     *   contre plusieurs requêtes simultanées.
     */
    public function track(
        Property $property,
        ?UserInterface $user,
        Response $response,
    ): bool {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request instanceof Request) {
            return false;
        }

        /*
         * On ne comptabilise que l'affichage réel de la page.
         *
         * Un POST du formulaire de contact ne doit pas
         * créer une nouvelle vue.
         */
        if (!$request->isMethod('GET')) {
            return false;
        }

        /*
         * Le bien doit obligatoirement être enregistré.
         */
        if (null === $property->getId()) {
            return false;
        }

        /*
         * Ne pas compter les requêtes de préchargement navigateur.
         */
        if ($this->isPrefetch($request)) {
            return false;
        }

        /*
         * Ne pas compter les robots connus.
         */
        if ($this->isBot($request)) {
            return false;
        }

        /*
         * Ne jamais compter le propriétaire de l'annonce.
         */
        if (
            $user instanceof User
            && $this->isPropertyOwner($property, $user)
        ) {
            return false;
        }

        /**
         * Création de l'empreinte visiteur.
         */
        $visitorHash = $this->createVisitorHash(
            request: $request,
            user: $user,
            response: $response,
        );

        /**
         * Création de la clé unique :
         *
         * propriété + visiteur + journée
         */
        $viewKey = $this->createViewKey(
            property: $property,
            visitorHash: $visitorHash,
        );

        /**
         * Première protection :
         * vérification applicative.
         */
        $existingView = $this->propertyViewRepository->findOneBy([
            'viewKey' => $viewKey,
        ]);

        if ($existingView instanceof PropertyView) {
            return false;
        }

        /**
         * Création de la vue.
         */
        $propertyView = new PropertyView();

        $propertyView
            ->setProperty($property)
            ->setUser(
                $user instanceof User
                    ? $user
                    : null
            )
            ->setVisitorHash($visitorHash)
            ->setViewKey($viewKey);

        $this->entityManager->persist($propertyView);

        try {
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException) {
            /*
             * Deuxième protection.
             *
             * Si deux requêtes arrivent exactement au même moment,
             * la contrainte UNIQUE view_key empêche le doublon.
             */
            return false;
        }

        return true;
    }

    /**
     * Crée l'identifiant haché du visiteur.
     */
    private function createVisitorHash(
        Request $request,
        ?UserInterface $user,
        Response $response,
    ): string {
        /*
         * Utilisateur connecté.
         *
         * On utilise son ID.
         *
         * Ainsi, même s'il supprime ses cookies ou utilise
         * plusieurs onglets, il reste le même utilisateur.
         */
        if (
            $user instanceof User
            && null !== $user->getId()
        ) {
            return hash_hmac(
                'sha256',
                'user:'.$user->getId(),
                $this->kernelSecret,
            );
        }

        /**
         * Visiteur anonyme.
         */
        $visitorId = $this->getOrCreateVisitorId(
            request: $request,
            response: $response,
        );

        return hash_hmac(
            'sha256',
            'anonymous:'.$visitorId,
            $this->kernelSecret,
        );
    }

    /**
     * Récupère ou crée l'identifiant anonyme permanent
     * du navigateur.
     */
    private function getOrCreateVisitorId(
        Request $request,
        Response $response,
    ): string {
        $visitorId = $request->cookies->get(
            self::COOKIE_NAME
        );

        /*
         * Le cookie existe et contient un UUID valide.
         */
        if (
            \is_string($visitorId)
            && Uuid::isValid($visitorId)
        ) {
            return $visitorId;
        }

        /**
         * Nouveau visiteur.
         */
        $visitorId = Uuid::v7()->toRfc4122();

        /**
         * IMPORTANT :
         *
         * On écrit réellement le cookie dans la réponse.
         *
         * C'est ce qui manquait dans ton ancienne version.
         */
        $cookie = Cookie::create(self::COOKIE_NAME)
            ->withValue($visitorId)
            ->withExpires(
                new \DateTimeImmutable(self::COOKIE_LIFETIME)
            )
            ->withPath('/')
            ->withSecure($request->isSecure())
            ->withHttpOnly(true)
            ->withSameSite(Cookie::SAMESITE_LAX);

        $response->headers->setCookie($cookie);

        return $visitorId;
    }

    /**
     * Crée une clé unique pour une vue.
     *
     * Une seule vue :
     *
     * propriété
     * + visiteur
     * + journée
     */
    private function createViewKey(
        Property $property,
        string $visitorHash,
    ): string {
        $now = new \DateTimeImmutable();

        return hash_hmac(
            'sha256',
            implode('|', [
                'property',
                (string) $property->getId(),
                $visitorHash,
                $now->format('Y-m-d'),
            ]),
            $this->kernelSecret,
        );
    }

    /**
     * Vérifie si l'utilisateur connecté
     * est propriétaire de l'annonce.
     */
    private function isPropertyOwner(
        Property $property,
        User $user,
    ): bool {
        $owner = $property->getUser();

        if (!$owner instanceof User) {
            return false;
        }

        return $owner->getId() === $user->getId();
    }

    /**
     * Détection des robots les plus courants.
     */
    private function isBot(Request $request): bool
    {
        $userAgent = mb_strtolower(
            mb_trim(
                $request->headers->get(
                    'User-Agent',
                    ''
                )
            )
        );

        /*
         * Pas de User-Agent :
         * on préfère ne pas compter.
         */
        if ('' === $userAgent) {
            return true;
        }

        $bots = [
            'bot',
            'crawler',
            'spider',
            'slurp',
            'googlebot',
            'bingbot',
            'facebookexternalhit',
            'twitterbot',
            'linkedinbot',
            'yandex',
            'baiduspider',
            'duckduckbot',
            'applebot',
            'semrush',
            'ahrefs',
            'mj12bot',
            'petalbot',
        ];

        foreach ($bots as $bot) {
            if (str_contains($userAgent, $bot)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Empêche les préchargements navigateur
     * de créer artificiellement une vue.
     */
    private function isPrefetch(Request $request): bool
    {
        $purpose = mb_strtolower(
            $request->headers->get('Purpose', '')
        );

        $secPurpose = mb_strtolower(
            $request->headers->get('Sec-Purpose', '')
        );

        return str_contains($purpose, 'prefetch')
            || str_contains($secPurpose, 'prefetch');
    }
}
