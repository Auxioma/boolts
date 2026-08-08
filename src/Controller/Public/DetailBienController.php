<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency
 * pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 *
 * Ce code source est la propriété exclusive de Auxioma Web Agency
 * et Pastelit Co.
 */

namespace App\Controller\Public;

use App\Entity\FormContact\Contact;
use App\Entity\Property;
use App\Form\FormContact\ContactType;
use App\Repository\PropertyRepository;
use App\Service\ContactForm\ContactMailer;
use App\Service\PropertyViewTracker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module Public / DetailBienController.
 */
final class DetailBienController extends AbstractController
{
    public function __construct(
        private readonly ContactMailer $contactMailer,
        private readonly EntityManagerInterface $entityManager,
        private readonly PropertyViewTracker $propertyViewTracker,
    ) {
    }

    #[Route(
        '/public/detail/bien/{slug}',
        name: 'app_public_detail_bien'
    )]
    public function index(
        #[MapEntity(mapping: ['slug' => 'slug'])]
        Property $property,
        PropertyRepository $propertyRepository,
        Request $request,
    ): Response {
        /**
         * Agence propriétaire du bien.
         */
        $agency = $property->getUser();

        /**
         * Biens similaires.
         */
        $bienSimilaire = $propertyRepository
            ->getBienSimilaire($property);

        /**
         * Formulaire de contact.
         */
        $contactForm = new Contact();

        $form = $this->createForm(
            ContactType::class,
            $contactForm
        );

        $form->handleRequest($request);

        /**
         * Traitement du formulaire.
         */
        if (
            $form->isSubmitted()
            && $form->isValid()
        ) {
            if ($agency === null) {
                throw $this->createNotFoundException(
                    'Aucune agence n’est associée à ce bien.'
                );
            }

            /**
             * Association du message à l'agence.
             */
            $contactForm->setAgence($agency);

            /**
             * Enregistrement.
             */
            $this->entityManager->persist($contactForm);
            $this->entityManager->flush();

            /**
             * Envoi du mail.
             */
            $this->contactMailer->sendContactMessage(
                contact: $contactForm,
                agencyEmail: $agency->getEmail(),
            );

            $this->addFlash(
                'success',
                'Votre message a été envoyé avec succès !'
            );
        }

        /**
         * Création de la réponse.
         *
         * IMPORTANT :
         * on doit créer la Response AVANT le tracking,
         * car PropertyViewTracker doit pouvoir ajouter
         * le cookie boolts_visitor_id.
         */
        $response = $this->render(
            'public/detail_bien/index.html.twig',
            [
                'property' => $property,

                /**
                 * Biens similaires.
                 */
                'properties' => $bienSimilaire,

                /**
                 * Formulaire.
                 */
                'form' => $form->createView(),
            ]
        );

        /**
         * Enregistrement sécurisé de la vue.
         *
         * Le service détermine automatiquement :
         *
         * - utilisateur connecté ;
         * - visiteur anonyme ;
         * - propriétaire ;
         * - robot ;
         * - refresh ;
         * - vue déjà comptabilisée aujourd'hui.
         */
        $this->propertyViewTracker->track(
            property: $property,
            user: $this->getUser(),
            response: $response,
        );

        return $response;
    }
}
