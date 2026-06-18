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

use App\Entity\FormContact\Contact;
use App\Entity\Property;
use App\Form\FormContact\ContactType;
use App\Repository\PropertyRepository;
use App\Service\ContactForm\ContactMailer;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailBienController extends AbstractController
{
    public function __construct(
        private readonly ContactMailer $contactMailer,
    ) {
    }

    #[Route('/public/detail/bien/{slug}', name: 'app_public_detail_bien')]
    public function index(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Property $property,
        PropertyRepository $propertyRepository,
        Request $request): Response
    {
        /**
         * Je récupere des données pour les biens similaire.
         */
        $bienSimilaire = $propertyRepository->getBienSimilaire($property);

        /**
         * Formulaire de contact.
         */
        $contactForm = new Contact();
        $form = $this->createForm(ContactType::class, $contactForm);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->contactMailer->sendContactMessage(
                contact: $contactForm,
                agencyEmail: $user->getEmail()
            );

            /* enregistrement dans la base de donnée */
            $contactForm->setAgence($user);
            $entityManager->persist($contactForm);
            $entityManager->flush();

            $this->addFlash('success', 'Votre message a été envoyé avec succès !');
        }

        return $this->render('public/detail_bien/index.html.twig', [
            'property' => $property,
            'properties' => $bienSimilaire, /* Bien similaire */
            'form' => $form->createView(),
        ]);
    }
}
