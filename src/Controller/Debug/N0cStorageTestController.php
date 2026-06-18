<?php

namespace App\Controller\Debug;

use League\Flysystem\FilesystemOperator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class N0cStorageTestController extends AbstractController
{
    public function __construct(
        #[Autowire(service: 'avatars.storage')]
        private readonly FilesystemOperator $avatarsStorage,

        #[Autowire(service: 'biens.storage')]
        private readonly FilesystemOperator $biensStorage,

        #[Autowire('%env(N0C_PUBLIC_URL)%')]
        private readonly string $n0cPublicUrl,
    ) {
    }

    #[Route('/debug/n0c/avatar', name: 'debug_n0c_avatar', methods: ['GET'])]
    public function avatar(): Response
    {
        $filename = 'test-avatar-' . date('Ymd-His') . '.txt';

        $this->avatarsStorage->write(
            $filename,
            'Test avatar envoyé depuis Symfony vers N0C Storage.'
        );

        $url = rtrim($this->n0cPublicUrl, '/') . '/avatar/' . $filename;

        return new Response(
            '<h1>Avatar envoyé avec succès</h1>' .
            '<p>Fichier : <strong>' . $filename . '</strong></p>' .
            '<p>URL publique :</p>' .
            '<p><a href="' . $url . '" target="_blank">' . $url . '</a></p>'
        );
    }

    #[Route('/debug/n0c/bien', name: 'debug_n0c_bien', methods: ['GET'])]
    public function bien(): Response
    {
        $filename = 'test-bien-' . date('Ymd-His') . '.txt';

        $this->biensStorage->write(
            $filename,
            'Test bien envoyé depuis Symfony vers N0C Storage.'
        );

        $url = rtrim($this->n0cPublicUrl, '/') . '/bien/' . $filename;

        return new Response(
            '<h1>Bien envoyé avec succès</h1>' .
            '<p>Fichier : <strong>' . $filename . '</strong></p>' .
            '<p>URL publique :</p>' .
            '<p><a href="' . $url . '" target="_blank">' . $url . '</a></p>'
        );
    }

    #[Route('/debug/n0c/all', name: 'debug_n0c_all', methods: ['GET'])]
    public function all(): Response
    {
        $avatarFilename = 'test-avatar-' . date('Ymd-His') . '.txt';
        $bienFilename = 'test-bien-' . date('Ymd-His') . '.txt';

        $this->avatarsStorage->write(
            $avatarFilename,
            'Test avatar envoyé depuis Symfony vers N0C Storage.'
        );

        $this->biensStorage->write(
            $bienFilename,
            'Test bien envoyé depuis Symfony vers N0C Storage.'
        );

        $avatarUrl = rtrim($this->n0cPublicUrl, '/') . '/avatar/' . $avatarFilename;
        $bienUrl = rtrim($this->n0cPublicUrl, '/') . '/bien/' . $bienFilename;

        return new Response(
            '<h1>Tests N0C envoyés avec succès</h1>' .

            '<h2>Avatar</h2>' .
            '<p>Fichier : <strong>' . $avatarFilename . '</strong></p>' .
            '<p><a href="' . $avatarUrl . '" target="_blank">' . $avatarUrl . '</a></p>' .

            '<h2>Bien</h2>' .
            '<p>Fichier : <strong>' . $bienFilename . '</strong></p>' .
            '<p><a href="' . $bienUrl . '" target="_blank">' . $bienUrl . '</a></p>'
        );
    }
}