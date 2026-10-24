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

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class HtmlMinifierSubscriber implements EventSubscriberInterface
{
    /*
     * Balises dont le contenu ne doit jamais être modifié :
     * les espaces et retours à la ligne y sont significatifs
     * (un commentaire « // » en JS inline casserait le script).
     */
    private const PROTECTED_TAGS_PATTERN = '#<(script|style|pre|textarea)\b[^>]*>.*?</\1\s*>#is';

    /*
     * Commentaires HTML, sauf commentaires conditionnels.
     */
    private const COMMENT_PATTERN = '#<!--(?!\[if|<!\[).*?-->#s';

    /*
     * Espaces au sens HTML uniquement (pas l’espace insécable).
     */
    private const WHITESPACE_PATTERN = '#[ \t\r\n\f]+#';

    public static function getSubscribedEvents(): array
    {
        return [
            /*
             * Priorité très basse : passer après l’injection
             * de la web debug toolbar (-128) et des autres listeners.
             */
            KernelEvents::RESPONSE => ['onKernelResponse', -256],
        ];
    }

    public function onKernelResponse(ResponseEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $response = $event->getResponse();

        if (
            $response instanceof StreamedResponse
            || $response instanceof BinaryFileResponse
        ) {
            return;
        }

        $contentType = (string) $response->headers->get('Content-Type', '');

        if (!str_starts_with(strtolower($contentType), 'text/html')) {
            return;
        }

        $content = $response->getContent();

        if (false === $content || '' === $content) {
            return;
        }

        $response->setContent($this->minify($content));
    }

    private function minify(string $html): string
    {
        $protectedBlocks = [];

        /*
         * Remplacer les blocs protégés par des marqueurs
         * sans espace, restaurés à la fin.
         */
        $minified = preg_replace_callback(
            self::PROTECTED_TAGS_PATTERN,
            static function (array $matches) use (&$protectedBlocks): string {
                $protectedBlocks[] = $matches[0];

                return "\x1A".(\count($protectedBlocks) - 1)."\x1A";
            },
            $html
        );

        if (null === $minified) {
            return $html;
        }

        $minified = preg_replace(self::COMMENT_PATTERN, '', $minified);

        if (null === $minified) {
            return $html;
        }

        /*
         * Réduire chaque suite d’espaces à un seul espace
         * (et non à rien) : le rendu des éléments inline
         * reste identique.
         */
        $minified = preg_replace(self::WHITESPACE_PATTERN, ' ', $minified);

        if (null === $minified) {
            return $html;
        }

        $minified = preg_replace_callback(
            "#\x1A(\d+)\x1A#",
            static fn (array $matches): string => $protectedBlocks[(int) $matches[1]],
            trim($minified)
        );

        return $minified ?? $html;
    }
}
