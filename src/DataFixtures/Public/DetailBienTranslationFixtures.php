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

final class DetailBienTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'detail_bien.meta.title' => 'Détail du bien',
                'detail_bien.topbar.back' => 'Retour',
                'detail_bien.topbar.favorite' => 'Favori',
                'detail_bien.photo.alt' => 'Photo %index% de l’annonce',
                'detail_bien.photo.main_alt' => 'Photo principale de l’annonce',
                'detail_bien.photo.listing_alt' => 'Photo de l’annonce',
                'detail_bien.photo.number_alt' => 'Photo %index%',
                'detail_bien.gallery.open' => 'Ouvrir la galerie photos',
                'detail_bien.gallery.open_photo' => 'Ouvrir la photo %index%',
                'detail_bien.gallery.see_all' => 'Voir les %count% photos',
                'detail_bien.gallery.back' => 'Retour',
                'detail_bien.gallery.close' => 'Fermer',
                'detail_bien.gallery.previous' => 'Image précédente',
                'detail_bien.gallery.next' => 'Image suivante',
                'detail_bien.head.title' => '%type% à %transaction%, %district%, %country%',
                'detail_bien.head.bedrooms.one' => '%count% chambre',
                'detail_bien.head.bedrooms.other' => '%count% chambres',
                'detail_bien.head.bathrooms.one' => '%count% salle de bain',
                'detail_bien.head.bathrooms.other' => '%count% salles de bain',
                'detail_bien.head.published_at' => 'Annonce publiée le %date%',
                'detail_bien.description.title' => 'Description du bien',
                'detail_bien.features.title' => 'Ce que le bien propose',
                'detail_bien.energy.title' => 'Performance énergétique',
                'detail_bien.energy.dpe_title' => 'Diagnostic de performance énergétique (DPE)',
                'detail_bien.energy.dpe_value' => '%value% kWhEP/m²/an',
                'detail_bien.energy.ges_title' => 'Indice d’émission de gaz à effet de serre (GES)',
                'detail_bien.energy.ges_value' => '%value% kgCO2/m²/an',
                'detail_bien.energy.construction_year' => 'Année de construction',
                'detail_bien.energy.diagnostic_date' => 'Date du diagnostic',
                'detail_bien.costs.title' => 'Détails des coûts',
                'detail_bien.costs.rent_charges_included' => 'Loyer C. Comprises',
                'detail_bien.costs.including_charges' => 'dont charges',
                'detail_bien.costs.deposit' => 'Dépôt de garantie à la charge du locataire',
                'detail_bien.price.charges_included' => 'C. Comprises',
                'detail_bien.contact.agency_type' => 'Agence immobilière',
                'detail_bien.contact.show_number' => 'Afficher le numéro',
                'detail_bien.contact.whatsapp' => 'Contacter via Whatsapp',
                'detail_bien.contact.call' => 'Appeler l’agence',
                'detail_bien.contact.email' => 'Contacter par email',
                'detail_bien.hours.closed' => 'Fermé',
                'detail_bien.hours.close' => 'Fermer les horaires',
                'detail_bien.form.lastname.label' => 'Votre nom*',
                'detail_bien.form.lastname.placeholder' => 'Veuillez saisir votre nom',
                'detail_bien.form.firstname.label' => 'Votre prénom*',
                'detail_bien.form.firstname.placeholder' => 'Veuillez saisir votre prénom',
                'detail_bien.form.email.label' => 'Votre e-mail*',
                'detail_bien.form.email.placeholder' => 'Veuillez saisir votre e-mail',
                'detail_bien.form.message.label' => 'Votre message*',
                'detail_bien.form.message.placeholder' => 'Veuillez saisir votre message',
                'detail_bien.form.submit' => 'Envoyer le mail',
                'detail_bien.form.success' => 'Votre message a été envoyé avec succès !',
                'detail_bien.similar.title' => 'Autres biens similaires à %city%',
                'detail_bien.similar.aria_label' => 'Biens similaires',
                'detail_bien.similar.previous' => 'Précédent',
                'detail_bien.similar.next' => 'Suivant',
                'detail_bien.similar.card.title' => '%type% à %transaction%',
                'detail_bien.similar.card.per_month' => '/mois',
                'detail_bien.similar.card.charges_included' => 'C. Comprises',
                'detail_bien.similar.card.bedrooms.one' => '%count% chambre',
                'detail_bien.similar.card.bedrooms.other' => '%count% chambres',
                'detail_bien.similar.card.bathrooms.one' => '%count% salle de bains',
                'detail_bien.similar.card.bathrooms.other' => '%count% salles de bains',
                'detail_bien.tabbar.aria_label' => 'Navigation mobile',
                'detail_bien.tabbar.explore' => 'Explorer',
                'detail_bien.tabbar.favorites' => 'Favoris',
                'detail_bien.tabbar.messages' => 'Messagerie',
                'detail_bien.tabbar.account' => 'Compte',
            ],
            'en' => [
                'detail_bien.meta.title' => 'Property details',
                'detail_bien.topbar.back' => 'Back',
                'detail_bien.topbar.favorite' => 'Favorite',
                'detail_bien.photo.alt' => 'Listing photo %index%',
                'detail_bien.photo.main_alt' => 'Main listing photo',
                'detail_bien.photo.listing_alt' => 'Listing photo',
                'detail_bien.photo.number_alt' => 'Photo %index%',
                'detail_bien.gallery.open' => 'Open photo gallery',
                'detail_bien.gallery.open_photo' => 'Open photo %index%',
                'detail_bien.gallery.see_all' => 'See all %count% photos',
                'detail_bien.gallery.back' => 'Back',
                'detail_bien.gallery.close' => 'Close',
                'detail_bien.gallery.previous' => 'Previous image',
                'detail_bien.gallery.next' => 'Next image',
                'detail_bien.head.title' => '%type% for %transaction%, %district%, %country%',
                'detail_bien.head.bedrooms.one' => '%count% bedroom',
                'detail_bien.head.bedrooms.other' => '%count% bedrooms',
                'detail_bien.head.bathrooms.one' => '%count% bathroom',
                'detail_bien.head.bathrooms.other' => '%count% bathrooms',
                'detail_bien.head.published_at' => 'Listing published on %date%',
                'detail_bien.description.title' => 'Property description',
                'detail_bien.features.title' => 'What this property offers',
                'detail_bien.energy.title' => 'Energy performance',
                'detail_bien.energy.dpe_title' => 'Energy performance certificate (EPC)',
                'detail_bien.energy.dpe_value' => '%value% kWhPE/m²/year',
                'detail_bien.energy.ges_title' => 'Greenhouse gas emissions index (GHG)',
                'detail_bien.energy.ges_value' => '%value% kgCO2/m²/year',
                'detail_bien.energy.construction_year' => 'Year of construction',
                'detail_bien.energy.diagnostic_date' => 'Diagnostic date',
                'detail_bien.costs.title' => 'Cost details',
                'detail_bien.costs.rent_charges_included' => 'Rent incl. charges',
                'detail_bien.costs.including_charges' => 'including charges',
                'detail_bien.costs.deposit' => 'Security deposit payable by the tenant',
                'detail_bien.price.charges_included' => 'Charges incl.',
                'detail_bien.contact.agency_type' => 'Real estate agency',
                'detail_bien.contact.show_number' => 'Show number',
                'detail_bien.contact.whatsapp' => 'Contact via WhatsApp',
                'detail_bien.contact.call' => 'Call the agency',
                'detail_bien.contact.email' => 'Contact by email',
                'detail_bien.hours.closed' => 'Closed',
                'detail_bien.hours.close' => 'Close opening hours',
                'detail_bien.form.lastname.label' => 'Your last name*',
                'detail_bien.form.lastname.placeholder' => 'Please enter your last name',
                'detail_bien.form.firstname.label' => 'Your first name*',
                'detail_bien.form.firstname.placeholder' => 'Please enter your first name',
                'detail_bien.form.email.label' => 'Your email*',
                'detail_bien.form.email.placeholder' => 'Please enter your email',
                'detail_bien.form.message.label' => 'Your message*',
                'detail_bien.form.message.placeholder' => 'Please enter your message',
                'detail_bien.form.submit' => 'Send email',
                'detail_bien.form.success' => 'Your message has been sent successfully!',
                'detail_bien.similar.title' => 'Other similar properties in %city%',
                'detail_bien.similar.aria_label' => 'Similar properties',
                'detail_bien.similar.previous' => 'Previous',
                'detail_bien.similar.next' => 'Next',
                'detail_bien.similar.card.title' => '%type% for %transaction%',
                'detail_bien.similar.card.per_month' => '/month',
                'detail_bien.similar.card.charges_included' => 'Charges incl.',
                'detail_bien.similar.card.bedrooms.one' => '%count% bedroom',
                'detail_bien.similar.card.bedrooms.other' => '%count% bedrooms',
                'detail_bien.similar.card.bathrooms.one' => '%count% bathroom',
                'detail_bien.similar.card.bathrooms.other' => '%count% bathrooms',
                'detail_bien.tabbar.aria_label' => 'Mobile navigation',
                'detail_bien.tabbar.explore' => 'Explore',
                'detail_bien.tabbar.favorites' => 'Favorites',
                'detail_bien.tabbar.messages' => 'Messages',
                'detail_bien.tabbar.account' => 'Account',
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
                $translation->setPage('detail_bien');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
