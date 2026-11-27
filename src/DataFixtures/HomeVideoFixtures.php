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

namespace App\DataFixtures;

use App\Entity\HomeVideo;
use App\Service\Intl\VisitorLocaleResolver;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Vidéos de fond de la home : pour chaque langue du site, 2 vidéos différentes
 * tirées au hasard parmi public/video/Boolts-1.mp4 … Boolts-28.mp4
 * (posters : public/video/poster-N.jpg).
 */
final class HomeVideoFixtures extends Fixture
{
    private const VIDEO_COUNT = 28;

    public function load(ObjectManager $manager): void
    {
        foreach (VisitorLocaleResolver::SUPPORTED as $locale) {
            // 2 numéros distincts au hasard entre 1 et 28
            $numbers = (array) array_rand(array_flip(range(1, self::VIDEO_COUNT)), \count(HomeVideo::SLOTS));
            shuffle($numbers);

            foreach (HomeVideo::SLOTS as $index => $emplacement) {
                $number = $numbers[$index];

                /** @var HomeVideo $video */
                $video = FixtureEntityHelper::findOrCreate($manager, HomeVideo::class, [
                    'locale' => $locale,
                    'emplacement' => $emplacement,
                ]);

                $video
                    ->setLocale($locale)
                    ->setEmplacement($emplacement)
                    ->setVideoName('Boolts-'.$number.'.mp4')
                    ->setPosterName('poster-'.$number.'.jpg');

                $manager->persist($video);
            }
        }

        $manager->flush();
    }
}
