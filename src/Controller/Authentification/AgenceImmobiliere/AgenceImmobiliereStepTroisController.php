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

namespace App\Controller\Authentification\AgenceImmobiliere;

use App\Entity\HoraireOuverture;
use App\Entity\User;
use App\Form\Authentification\CompleteProfileType;
use App\Form\Authentification\StepCinqType;
use App\Form\Authentification\StepQuatreType;
use App\Form\Authentification\StepSixType;
use App\Repository\UserRepository;
use App\Security\AgenceImmobiliereAuthenticator;
use App\Service\Authentification\AgencyRegistrationProgress;
use App\Service\Billing\FreeAgencySubscriptionActivator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

final class AgenceImmobiliereStepTroisController extends AbstractController
{
    private const TRANSLATION_LOCALES = [
        'fr',
        'en',
    ];

    private const OPENING_DAYS = [
        'lundi',
        'mardi',
        'mercredi',
        'jeudi',
        'vendredi',
        'samedi',
        'dimanche',
    ];

    #[Route(
        path: [
            'fr' => '/fr/pro/step3',
            'en' => '/pro/step3',
        ],
        name: 'app_professionnelle_step_trois'
    )]
    public function step3(Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        Security $security,
        UserPasswordHasherInterface $userPasswordHasher,
        FreeAgencySubscriptionActivator $freeAgencySubscriptionActivator,
        AgencyRegistrationProgress $agencyRegistrationProgress,
    ): Response
    {
        $registrationUser = $this->getRegistrationUser(
            $request,
            $userRepository,
            $agencyRegistrationProgress,
            AgencyRegistrationProgress::STEP_PROFILE,
        );

        if (!$registrationUser instanceof User) {
            return $registrationUser;
        }

        $user = $registrationUser;
        $form = $this->createForm(CompleteProfileType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setNumeroContact($this->normalizeOptionalText($user->getTelephone()));

            $user
                ->setIsVerified(false)
                ->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_ADDRESS)
            ;
            $freeAgencySubscriptionActivator->activate($user);
            $em->flush();

            $request->getSession()->set('auth_step', AgencyRegistrationProgress::STEP_ADDRESS);

            return $this->redirectToRoute('app_professionnelle_step_quatre');
        }

        return $this->render('authentification/agence_immobiliere/step3.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/step4',
            'en' => '/pro/step4',
        ],
        name: 'app_professionnelle_step_quatre'
    )]
    public function step4(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        AgencyRegistrationProgress $agencyRegistrationProgress,
    ): Response
    {
        $registrationUser = $this->getRegistrationUser(
            $request,
            $userRepository,
            $agencyRegistrationProgress,
            AgencyRegistrationProgress::STEP_ADDRESS,
        );

        if (!$registrationUser instanceof User) {
            return $registrationUser;
        }

        $user = $registrationUser;
        $form = $this->createForm(StepQuatreType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $adresse = $form->get('adresse')->getData();
            $adresseComplement = $form->get('adresseComplement')->getData();
            $ville = $form->get('ville')->getData();
            $pays = $user->getPays();

            $this->syncUserTranslations($user, [
                'adresse' => $adresse,
                'adresseComplement' => $adresseComplement,
                'ville' => $ville,
                'adresseContact' => $adresse,
                'adresseComplementContact' => $adresseComplement,
                'villeContact' => $ville,
                'paysContact' => $pays?->getNom(),
            ]);

            $user
                ->setCodePostalContact($this->normalizeOptionalText($form->get('codePostal')->getData()))
                ->setNumeroContact($this->normalizeOptionalText($user->getTelephone()))
                ->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_PRESENTATION)
            ;
            $em->flush();

            $request->getSession()->set('auth_step', AgencyRegistrationProgress::STEP_PRESENTATION);

            return $this->redirectToRoute('app_professionnelle_step_cinq');
        }

        return $this->render('authentification/agence_immobiliere/step4.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/step5',
            'en' => '/pro/step5',
        ],
        name: 'app_professionnelle_step_cinq'
    )]
    public function step5(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        AgencyRegistrationProgress $agencyRegistrationProgress,
    ): Response
    {
        $registrationUser = $this->getRegistrationUser(
            $request,
            $userRepository,
            $agencyRegistrationProgress,
            AgencyRegistrationProgress::STEP_PRESENTATION,
        );

        if (!$registrationUser instanceof User) {
            return $registrationUser;
        }

        $user = $registrationUser;
        $form = $this->createForm(StepCinqType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->syncUserTranslations($user, [
                'description' => $form->get('description')->getData(),
            ]);

            $user->setAgencyRegistrationStep(AgencyRegistrationProgress::STEP_OPENING_HOURS);
            $em->flush();

            $request->getSession()->set('auth_step', AgencyRegistrationProgress::STEP_OPENING_HOURS);

            return $this->redirectToRoute('app_professionnelle_step_six');
        }

        return $this->render('authentification/agence_immobiliere/step5.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    #[Route(
        path: [
            'fr' => '/fr/pro/step6',
            'en' => '/pro/step6',
        ],
        name: 'app_professionnelle_step_six'
    )]
    public function step6(
        Request $request,
        UserRepository $userRepository,
        EntityManagerInterface $em,
        Security $security,
        AgencyRegistrationProgress $agencyRegistrationProgress,
    ): Response
    {
        $registrationUser = $this->getRegistrationUser(
            $request,
            $userRepository,
            $agencyRegistrationProgress,
            AgencyRegistrationProgress::STEP_OPENING_HOURS,
        );

        if (!$registrationUser instanceof User) {
            return $registrationUser;
        }

        $user = $registrationUser;
        $this->ensureOpeningHours($user, $em);

        $form = $this->createForm(StepSixType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            foreach ($user->getHoraireOuvertures() as $horaireOuverture) {
                $em->persist($horaireOuverture);

                if (!$horaireOuverture->isOpen()) {
                    $horaireOuverture
                        ->setOuvertureMatin(null)
                        ->setFermetureMatin(null)
                        ->setOuvertureApresMidi(null)
                        ->setFermetureApresMidi(null)
                    ;
                }
            }

            $user->setIsVerified(true);
            $user->setAgencyRegistrationStep(null);
            $em->flush();

            $security->login($user, AgenceImmobiliereAuthenticator::class, 'main');

            $session = $request->getSession();
            $session->remove('auth_user_id');
            $session->remove('auth_step');

            return $this->redirectToRoute('agence_immobiliere_dashboard');
        }

        return $this->render('authentification/agence_immobiliere/step6.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    private function getRegistrationUser(
        Request $request,
        UserRepository $userRepository,
        AgencyRegistrationProgress $agencyRegistrationProgress,
        string $expectedStep,
    ): User|Response {
        $session = $request->getSession();
        $authUserId = $session->get('auth_user_id');

        if (!$authUserId) {
            return $this->redirectToRoute('app_professionnelle_register');
        }

        $user = $userRepository->find($authUserId);

        if (!$user instanceof User) {
            $session->remove('auth_user_id');
            $session->remove('auth_step');

            return $this->redirectToRoute('app_professionnelle_register');
        }

        if (!$agencyRegistrationProgress->isIncompleteAgencyRegistration($user)) {
            $session->remove('auth_user_id');
            $session->remove('auth_step');

            return $this->redirectToRoute('app_professionnelle_connexion');
        }

        $expectedRoute = $agencyRegistrationProgress->routeForStep($expectedStep);
        $currentRoute = $agencyRegistrationProgress->routeForCurrentStep($user);

        if ($currentRoute !== $expectedRoute) {
            return $this->redirectToRoute($currentRoute);
        }

        return $user;
    }

    /**
     * @param array<string, ?string> $values
     */
    private function syncUserTranslations(User $user, array $values): void
    {
        foreach (self::TRANSLATION_LOCALES as $locale) {
            $translation = $user->translate($locale);

            foreach ($values as $field => $value) {
                match ($field) {
                    'adresse' => $translation->setAdresse($value),
                    'adresseComplement' => $translation->setAdresseComplement($value),
                    'ville' => $translation->setVille($value),
                    'description' => $translation->setDescription($value),
                    'adresseContact' => $translation->setAdresseContact($value),
                    'adresseComplementContact' => $translation->setAdresseComplementContact($value),
                    'villeContact' => $translation->setVilleContact($value),
                    'paysContact' => $translation->setPaysContact($value),
                    default => throw new \LogicException(sprintf('Champ de traduction User non supporté : %s.', $field)),
                };
            }
        }

        $user->mergeNewTranslations();
    }

    private function normalizeOptionalText(?string $value): ?string
    {
        if (null === $value) {
            return null;
        }

        $value = mb_trim($value);

        return '' === $value ? null : $value;
    }

    private function ensureOpeningHours(User $user, EntityManagerInterface $em): void
    {
        $existingDays = [];

        foreach ($user->getHoraireOuvertures() as $horaireOuverture) {
            if (!$horaireOuverture->getJour()) {
                continue;
            }

            $existingDays[$horaireOuverture->getJour()] = true;
        }

        foreach (self::OPENING_DAYS as $day) {
            if (isset($existingDays[$day])) {
                continue;
            }

            $horaireOuverture = (new HoraireOuverture())
                ->setJour($day)
            ;

            $user->addHoraireOuverture($horaireOuverture);
            $em->persist($horaireOuverture);
        }
    }
}
