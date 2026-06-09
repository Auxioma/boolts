<?php

namespace App\Controller\Public;

use App\Entity\FormContact\Contact;
use App\Form\FormContact\ContactType;
use App\Repository\PropertyRepository;
use App\Repository\UserRepository;
use App\Service\ContactForm\ContactMailer;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class DetailAgenceController extends AbstractController
{
    public function __construct(
        private readonly ContactMailer $contactMailer,
    ) {
    }

    #[Route('/agency/{slug}', name: 'app_public_detail_agence')]
    public function index(
        UserRepository $userRepository,
        PropertyRepository $propertyRepository,
        string $slug,
        PaginatorInterface $paginator,
        Request $request,
        EntityManagerInterface $entityManager,
    ): Response {
        $user = $userRepository->findOneBy(['slug' => $slug]);

        if (!$user) {
            throw $this->createNotFoundException('Agence introuvable.');
        }

        $properties = $paginator->paginate(
            $propertyRepository->findBy(['user' => $user]),
            $request->query->getInt('page', 1),
            8
        );

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
        ]);
    }
}
