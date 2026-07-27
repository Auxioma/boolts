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
use App\Entity\AgencyProfileDailyVisit;
use App\Entity\User;
use App\Form\FormContact\ContactType;
use App\Repository\AgencyProfileDailyVisitRepository;
use App\Repository\FavorisRepository;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use App\Service\ContactForm\ContactMailer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * HTTP controller for module Public / DetailAgenceController.
 *
 * Centralizes actions exposed by the routes declared in this class.
 */
final class DetailAgenceController extends AbstractController
{
    /**
     * Handles the __construct controller action.
     */
    public function __construct(
        private readonly ContactMailer $contactMailer,
    ) {
    }

    #[Route('/agency/{slug}', name: 'app_public_detail_agence')]
    /**
     * Handles the index controller action.
     */
    public function index(
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        FavorisRepository $favorisRepository,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
        string $slug,
        PaginatorInterface $paginator,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            throw $this->createNotFoundException('Agence introuvable.');
        }

        $this->recordProfileVisit($user, $agencyProfileDailyVisitRepository, $entityManager);

        /**
         * Gestion des filtre avec la pagination.
         */
        $sort = $request->query->get('sort', 'p.createdAt');
        $direction = mb_strtolower($request->query->get('direction', 'desc'));

        $allowedSorts = [
            'p.createdAt',
            'p.views',
            'favorisCount',
        ];

        if (!\in_array($sort, $allowedSorts, true)) {
            $sort = 'p.createdAt';
        }

        if (!\in_array($direction, ['asc', 'desc'], true)) {
            $direction = 'desc';
        }

        $properties = $paginator->paginate(
            $propertyRepository->findPropertysByUserQuery(
                user: $user,
                sort: $sort,
                direction: mb_strtoupper($direction)
            ),
            $request->query->getInt('page', 1),
            8,
            [
                'sortFieldParameterName' => '_sort',
                'sortDirectionParameterName' => '_direction',
            ]
        );

        /*
         * Liste des biens déjà ajoutés en favoris
         * par l'utilisateur connecté.
         *
         * Si le visiteur n'est pas connecté : tableau vide.
         * Si c'est une agence : tableau vide.
         */
        $favoritePropertyIds = [];

        if ($this->getUser() && !$this->isGranted('ROLE_AGENCE')) {
            $favoritePropertyIds = $favorisRepository->findPropertyIdsByUser($this->getUser());
        }

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

            return $this->redirectToRoute('app_public_detail_agence', [
                'slug' => $slug,
            ]);
        }

        return $this->render('public/detail_agence/index.html.twig', [
            'user' => $user,
            'properties' => $properties,
            'form' => $form->createView(),
            'favoritePropertyIds' => $favoritePropertyIds,
        ]);
    }

    private function recordProfileVisit(
        User $agency,
        AgencyProfileDailyVisitRepository $agencyProfileDailyVisitRepository,
        EntityManagerInterface $entityManager,
    ): void {
        $viewer = $this->getUser();

        if ($viewer instanceof User && $viewer->getId() === $agency->getId()) {
            return;
        }

        $today = new \DateTimeImmutable('today');
        $dailyVisit = $agencyProfileDailyVisitRepository->findOneBy([
            'agency' => $agency,
            'viewedOn' => $today,
        ]);

        if (!$dailyVisit instanceof AgencyProfileDailyVisit) {
            $dailyVisit = new AgencyProfileDailyVisit();
            $dailyVisit
                ->setAgency($agency)
                ->setViewedOn($today)
                ->setVisits(0);
            $entityManager->persist($dailyVisit);
        }

        $dailyVisit->incrementVisits();
        $agency->setVisitAgency($agency->getVisitAgency() + 1);
        $entityManager->flush();
    }
}
