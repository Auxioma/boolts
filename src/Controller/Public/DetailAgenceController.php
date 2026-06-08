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

namespace App\Controller\Public;

use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailAgenceController extends AbstractController
{
    #[Route('/agency/{slug}', name: 'app_public_detail_agence')]
    public function index(
        UserRepository $userRepository, 
        PropertyRepository $propertyRepository, 
        string $slug,
        PaginatorInterface $paginator, 
        Request $request
        ): Response
    {
        $user = $userRepository->findOneBy(['slug' => $slug]);

        $properties = $paginator->paginate(
            $propertyRepository->findBy(['user' => $user]),
            $request->query->getInt('page', 1), 
            8 
        );

        return $this->render('public/detail_agence/index.html.twig', [
            'user' => $user,
            'properties' => $properties,
        ]); 
    }
}
