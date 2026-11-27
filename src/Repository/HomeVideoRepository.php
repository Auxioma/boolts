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

namespace App\Repository;

use App\Entity\HomeVideo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HomeVideo>
 */
class HomeVideoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HomeVideo::class);
    }

    /**
     * Une vidéo tirée au hasard parmi celles de la langue (2 au maximum),
     * ou null si aucune vidéo n'est configurée pour cette langue.
     */
    public function findRandomForLocale(string $locale): ?HomeVideo
    {
        $videos = $this->createQueryBuilder('v')
            ->andWhere('v.locale = :locale')
            ->andWhere('v.videoName IS NOT NULL')
            ->setParameter('locale', $locale)
            ->getQuery()
            ->getResult();

        if ([] === $videos) {
            return null;
        }

        return $videos[array_rand($videos)];
    }
}
