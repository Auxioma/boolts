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

namespace App\DataFixtures\Public;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class NavbarTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'navbar.mobile.aria_label' => 'Ouvrir le menu',
                'navbar.buy' => 'Acheter',
                'navbar.rent' => 'Louer',
                'navbar.sell' => 'Vendre',
                'navbar.find_professional' => 'Trouver un pro',
                'navbar.exclusives' => 'Exclusivités',
                'navbar.submit_listing' => 'Déposer une annonce',
                'navbar.login.aria_label' => 'Se connecter',
                'navbar.account.aria_label' => 'Accéder à mon compte',
                'navbar.profile.alt' => 'Profil utilisateur',
                'navbar.mobile.navigation.aria_label' => 'Navigation mobile',
                'navbar.mobile.explore' => 'Explorer',
                'navbar.mobile.favorites' => 'Favoris',
                'navbar.mobile.messaging' => 'Messagerie',
                'navbar.mobile.login' => 'Connexion',
            ],
            'en' => [
                'navbar.mobile.aria_label' => 'Open menu',
                'navbar.buy' => 'Buy',
                'navbar.rent' => 'Rent',
                'navbar.sell' => 'Sell',
                'navbar.find_professional' => 'Find a professional',
                'navbar.exclusives' => 'Exclusives',
                'navbar.submit_listing' => 'Post a listing',
                'navbar.login.aria_label' => 'Sign in',
                'navbar.account.aria_label' => 'Access my account',
                'navbar.profile.alt' => 'User profile',
                'navbar.mobile.navigation.aria_label' => 'Mobile navigation',
                'navbar.mobile.explore' => 'Explore',
                'navbar.mobile.favorites' => 'Favorites',
                'navbar.mobile.messaging' => 'Messages',
                'navbar.mobile.login' => 'Sign in',
            ],
            'es' => [
                'navbar.mobile.aria_label' => 'Abrir el menú',
                'navbar.buy' => 'Comprar',
                'navbar.rent' => 'Alquilar',
                'navbar.sell' => 'Vender',
                'navbar.find_professional' => 'Encontrar un profesional',
                'navbar.exclusives' => 'Exclusivas',
                'navbar.submit_listing' => 'Publicar un anuncio',
                'navbar.login.aria_label' => 'Iniciar sesión',
                'navbar.account.aria_label' => 'Acceder a mi cuenta',
                'navbar.profile.alt' => 'Perfil de usuario',
                'navbar.mobile.navigation.aria_label' => 'Navegación móvil',
                'navbar.mobile.explore' => 'Explorar',
                'navbar.mobile.favorites' => 'Favoritos',
                'navbar.mobile.messaging' => 'Mensajes',
                'navbar.mobile.login' => 'Iniciar sesión',
            ],
            'de' => [
                'navbar.mobile.aria_label' => 'Menü öffnen',
                'navbar.buy' => 'Kaufen',
                'navbar.rent' => 'Mieten',
                'navbar.sell' => 'Verkaufen',
                'navbar.find_professional' => 'Profi finden',
                'navbar.exclusives' => 'Exklusiv',
                'navbar.submit_listing' => 'Anzeige aufgeben',
                'navbar.login.aria_label' => 'Anmelden',
                'navbar.account.aria_label' => 'Zu meinem Konto',
                'navbar.profile.alt' => 'Benutzerprofil',
                'navbar.mobile.navigation.aria_label' => 'Mobile Navigation',
                'navbar.mobile.explore' => 'Entdecken',
                'navbar.mobile.favorites' => 'Favoriten',
                'navbar.mobile.messaging' => 'Nachrichten',
                'navbar.mobile.login' => 'Anmelden',
            ],
            'pt' => [
                'navbar.mobile.aria_label' => 'Abrir o menu',
                'navbar.buy' => 'Comprar',
                'navbar.rent' => 'Arrendar',
                'navbar.sell' => 'Vender',
                'navbar.find_professional' => 'Encontrar um profissional',
                'navbar.exclusives' => 'Exclusivos',
                'navbar.submit_listing' => 'Publicar um anúncio',
                'navbar.login.aria_label' => 'Iniciar sessão',
                'navbar.account.aria_label' => 'Aceder à minha conta',
                'navbar.profile.alt' => 'Perfil de utilizador',
                'navbar.mobile.navigation.aria_label' => 'Navegação móvel',
                'navbar.mobile.explore' => 'Explorar',
                'navbar.mobile.favorites' => 'Favoritos',
                'navbar.mobile.messaging' => 'Mensagens',
                'navbar.mobile.login' => 'Iniciar sessão',
            ],
            'it' => [
                'navbar.mobile.aria_label' => 'Apri il menu',
                'navbar.buy' => 'Acquista',
                'navbar.rent' => 'Affittare',
                'navbar.sell' => 'Vendere',
                'navbar.find_professional' => 'Trova un professionista',
                'navbar.exclusives' => 'Esclusive',
                'navbar.submit_listing' => 'Pubblica un annuncio',
                'navbar.login.aria_label' => 'Accedi',
                'navbar.account.aria_label' => 'Accedi al mio account',
                'navbar.profile.alt' => 'Profilo utente',
                'navbar.mobile.navigation.aria_label' => 'Navigazione mobile',
                'navbar.mobile.explore' => 'Esplora',
                'navbar.mobile.favorites' => 'Preferiti',
                'navbar.mobile.messaging' => 'Messaggi',
                'navbar.mobile.login' => 'Accedi',
            ],
            'nl' => [
                'navbar.mobile.aria_label' => 'Menu openen',
                'navbar.buy' => 'Kopen',
                'navbar.rent' => 'Huren',
                'navbar.sell' => 'Verkopen',
                'navbar.find_professional' => 'Vind een professional',
                'navbar.exclusives' => 'Exclusief',
                'navbar.submit_listing' => 'Advertentie plaatsen',
                'navbar.login.aria_label' => 'Inloggen',
                'navbar.account.aria_label' => 'Naar mijn account',
                'navbar.profile.alt' => 'Gebruikersprofiel',
                'navbar.mobile.navigation.aria_label' => 'Mobiele navigatie',
                'navbar.mobile.explore' => 'Ontdekken',
                'navbar.mobile.favorites' => 'Favorieten',
                'navbar.mobile.messaging' => 'Berichten',
                'navbar.mobile.login' => 'Inloggen',
            ],
            'hu' => [
                'navbar.mobile.aria_label' => 'Menü megnyitása',
                'navbar.buy' => 'Vásárlás',
                'navbar.rent' => 'Bérlés',
                'navbar.sell' => 'Eladás',
                'navbar.find_professional' => 'Szakember keresése',
                'navbar.exclusives' => 'Exkluzív ajánlatok',
                'navbar.submit_listing' => 'Hirdetés feladása',
                'navbar.login.aria_label' => 'Bejelentkezés',
                'navbar.account.aria_label' => 'Fiókom megnyitása',
                'navbar.profile.alt' => 'Felhasználói profil',
                'navbar.mobile.navigation.aria_label' => 'Mobil navigáció',
                'navbar.mobile.explore' => 'Felfedezés',
                'navbar.mobile.favorites' => 'Kedvencek',
                'navbar.mobile.messaging' => 'Üzenetek',
                'navbar.mobile.login' => 'Bejelentkezés',
            ],
        ];

        foreach ($translations as $locale => $items) {
            foreach ($items as $key => $value) {
                $translation = FixtureEntityHelper::findOrCreate($manager, Translation::class, [
                    'translationKey' => $key,
                    'locale' => $locale,
                ]);
                $translation->setTranslationKey($key);
                $translation->setLocale($locale);
                $translation->setTranslation($value);
                $translation->setPage('navbar');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
