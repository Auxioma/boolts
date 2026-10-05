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

namespace App\Service\Import;

/**
 * Biens d'exemple inclus dans le modèle CSV téléchargeable.
 *
 * Les valeurs sont directement importables : types de bien, transactions et
 * caractéristiques reprennent les libellés existants en base, les adresses et
 * coordonnées sont réelles et les images pointent vers des photos publiques
 * (Unsplash). Seul l'e-mail de l'agence est injecté au moment de la génération.
 */
final class PropertyCsvTemplateExamples
{
    private const string IMAGE_URL = 'https://images.unsplash.com/photo-%s?w=1600&q=80';

    /**
     * @return list<array<string, string>> colonne du modèle => valeur
     */
    public static function rows(string $agencyEmail): array
    {
        $rows = [];

        foreach (self::properties() as $index => $property) {
            $translations = $property['i18n'];
            unset($property['i18n']);

            $property['images'] = implode('|', array_map(
                static fn (string $id): string => \sprintf(self::IMAGE_URL, $id),
                explode('|', $property['images']),
            ));

            $row = ['agence_email' => $agencyEmail, 'statut' => 'brouillon', 'code_iso_pays' => 'FR', 'feature_type' => 'address']
                + $property
                + ['reference_interne' => \sprintf('IMPORT-%03d', $index + 1)];

            foreach ($translations as $field => $values) {
                foreach ($values as $locale => $value) {
                    $row[$field.'_'.$locale] = $value;
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * @return list<array<string, mixed>>
     */
    private static function properties(): array
    {
        return [
            [
                'type_bien' => 'Appartement',
                'type_transaction' => 'Acheter',
                'surface_total' => '68,40',
                'prix' => '629000',
                'montant_des_charges' => '210',
                'annee_construction' => '1890',
                'code_postal' => '75011',
                'latitude' => '48.8546000',
                'longitude' => '2.3725000',
                'chambres' => '2',
                'salle_de_bains' => '1',
                'dpe' => '168', 'dpe_lettre' => 'C', 'ges' => '24', 'ges_lettre' => 'C',
                'date_indexation_energie' => '2025-03-12',
                'show_adresse' => 'non',
                'caracteristiques' => 'Ascenseur|Cave/débarras|Chauffage',
                'images' => '1560448204-e02f11c3d0e2|1502672260266-1c1ef2d93688',
                'i18n' => [
                    'titre' => ['fr' => 'Appartement haussmannien 3 pièces – Bastille', 'en' => 'Haussmann-style 2-bedroom apartment – Bastille'],
                    'description' => [
                        'fr' => 'Au 4e étage avec ascenseur d’un immeuble en pierre de taille, bel appartement traversant de 68 m² : séjour lumineux avec parquet, moulures et cheminée, cuisine équipée, deux chambres sur cour calme, salle d’eau. Cave. À deux pas de la place de la Bastille et du métro Voltaire.',
                        'en' => 'On the 4th floor with lift in a classic stone building, a bright dual-aspect 68 m² apartment: living room with parquet floors, mouldings and fireplace, fitted kitchen, two quiet bedrooms overlooking the courtyard, shower room. Cellar. A short walk from Place de la Bastille and Voltaire metro.',
                    ],
                    'adresse' => ['fr' => '18 Rue de la Roquette', 'en' => '18 Rue de la Roquette'],
                    'ville' => ['fr' => 'Paris', 'en' => 'Paris'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '18 Rue de la Roquette, 75011 Paris, France', 'en' => '18 Rue de la Roquette, 75011 Paris, France'],
                    'region' => ['fr' => 'Île-de-France', 'en' => 'Île-de-France'],
                    'district' => ['fr' => 'Paris', 'en' => 'Paris'],
                    'localite' => ['fr' => 'Paris 11e Arrondissement', 'en' => 'Paris 11th Arrondissement'],
                    'quartier' => ['fr' => 'Bastille', 'en' => 'Bastille'],
                    'point_interet' => ['fr' => 'Place de la Bastille', 'en' => 'Place de la Bastille'],
                ],
            ],
            [
                'type_bien' => 'Maison',
                'type_transaction' => 'Acheter',
                'surface_total' => '142',
                'prix' => '895000',
                'annee_construction' => '1925',
                'code_postal' => '33000',
                'latitude' => '44.8530000',
                'longitude' => '-0.5700000',
                'chambres' => '4',
                'salle_de_bains' => '2',
                'dpe' => '205', 'dpe_lettre' => 'D', 'ges' => '38', 'ges_lettre' => 'D',
                'date_indexation_energie' => '2024-11-05',
                'show_adresse' => 'non',
                'caracteristiques' => 'Jardin|Terrasse|Chauffage',
                'images' => '1568605114967-8130f3a36994|1564013799919-ab600027ffc6',
                'i18n' => [
                    'titre' => ['fr' => 'Échoppe bordelaise rénovée avec jardin – Chartrons', 'en' => 'Renovated Bordeaux townhouse with garden – Chartrons'],
                    'description' => [
                        'fr' => 'Échoppe double en pierre entièrement rénovée : grande pièce de vie de 55 m² ouverte sur un jardin arboré et sa terrasse plein sud, cuisine sur mesure, quatre chambres dont une suite parentale, deux salles de bains. Quartier des Chartrons, commerces et tram B à pied.',
                        'en' => 'Fully renovated double stone “échoppe”: 55 m² living area opening onto a leafy garden with south-facing terrace, bespoke kitchen, four bedrooms including a master suite, two bathrooms. Chartrons district, shops and tram line B within walking distance.',
                    ],
                    'adresse' => ['fr' => '25 Rue Notre-Dame', 'en' => '25 Rue Notre-Dame'],
                    'ville' => ['fr' => 'Bordeaux', 'en' => 'Bordeaux'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '25 Rue Notre-Dame, 33000 Bordeaux, France', 'en' => '25 Rue Notre-Dame, 33000 Bordeaux, France'],
                    'region' => ['fr' => 'Nouvelle-Aquitaine', 'en' => 'Nouvelle-Aquitaine'],
                    'district' => ['fr' => 'Gironde', 'en' => 'Gironde'],
                    'localite' => ['fr' => 'Bordeaux', 'en' => 'Bordeaux'],
                    'quartier' => ['fr' => 'Chartrons', 'en' => 'Chartrons'],
                    'point_interet' => ['fr' => 'Cité du Vin', 'en' => 'Cité du Vin'],
                ],
            ],
            [
                'type_bien' => 'Appartement',
                'type_transaction' => 'Louer',
                'surface_total' => '54',
                'montant_loyer_hors_charge' => '1150',
                'montant_des_charges' => '95',
                'montant_depot_de_garantie' => '1150',
                'annee_construction' => '1860',
                'code_postal' => '69002',
                'latitude' => '45.7605000',
                'longitude' => '4.8357000',
                'chambres' => '1',
                'salle_de_bains' => '1',
                'dpe' => '142', 'dpe_lettre' => 'C', 'ges' => '19', 'ges_lettre' => 'C',
                'date_indexation_energie' => '2025-06-20',
                'show_adresse' => 'oui',
                'caracteristiques' => 'Ascenseur|Chauffage',
                'images' => '1493809842364-78817add7ffb|1522708323590-d24dbb6b0267',
                'i18n' => [
                    'titre' => ['fr' => 'T2 meublé lumineux – Presqu’île', 'en' => 'Bright furnished 1-bedroom flat – Presqu’île'],
                    'description' => [
                        'fr' => 'Appartement de 54 m² entièrement meublé au 3e étage avec ascenseur : séjour avec vue dégagée sur la rue de la République, cuisine équipée ouverte, chambre avec placards, salle de douche. Disponible immédiatement, bail d’un an renouvelable.',
                        'en' => 'Fully furnished 54 m² apartment on the 3rd floor with lift: living room with open views over Rue de la République, open fitted kitchen, bedroom with built-in wardrobes, shower room. Available now, one-year renewable lease.',
                    ],
                    'adresse' => ['fr' => '48 Rue de la République', 'en' => '48 Rue de la République'],
                    'ville' => ['fr' => 'Lyon', 'en' => 'Lyon'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '48 Rue de la République, 69002 Lyon, France', 'en' => '48 Rue de la République, 69002 Lyon, France'],
                    'region' => ['fr' => 'Auvergne-Rhône-Alpes', 'en' => 'Auvergne-Rhône-Alpes'],
                    'district' => ['fr' => 'Rhône', 'en' => 'Rhône'],
                    'localite' => ['fr' => 'Lyon 2e Arrondissement', 'en' => 'Lyon 2nd Arrondissement'],
                    'quartier' => ['fr' => 'Presqu’île', 'en' => 'Presqu’île'],
                    'point_interet' => ['fr' => 'Place Bellecour', 'en' => 'Place Bellecour'],
                ],
            ],
            [
                'type_bien' => 'Villa',
                'type_transaction' => 'Acheter',
                'surface_total' => '260',
                'prix' => '3450000',
                'annee_construction' => '1972',
                'code_postal' => '06300',
                'latitude' => '43.6990000',
                'longitude' => '7.2930000',
                'chambres' => '5',
                'salle_de_bains' => '4',
                'dpe' => '98', 'dpe_lettre' => 'B', 'ges' => '9', 'ges_lettre' => 'B',
                'date_indexation_energie' => '2025-01-28',
                'show_adresse' => 'non',
                'caracteristiques' => 'Piscine|Jardin|Terrasse|Climatisation|Stationnement',
                'images' => '1512917774080-9991f1c4c750|1613490493576-7fde63acd811|1580587771525-78b9dba3b914',
                'i18n' => [
                    'titre' => ['fr' => 'Villa contemporaine vue mer – Mont Boron', 'en' => 'Contemporary sea-view villa – Mont Boron'],
                    'description' => [
                        'fr' => 'Sur les hauteurs du Mont Boron, villa d’architecte de 260 m² entièrement rénovée offrant une vue panoramique sur la baie des Anges. Vaste réception ouverte sur les terrasses, cinq chambres en suite, piscine à débordement chauffée, jardin paysager de 1 500 m², garage double.',
                        'en' => 'On the heights of Mont Boron, a fully renovated 260 m² architect-designed villa with panoramic views over the Baie des Anges. Large reception rooms opening onto terraces, five en-suite bedrooms, heated infinity pool, 1,500 m² landscaped garden, double garage.',
                    ],
                    'adresse' => ['fr' => 'Boulevard du Mont Boron', 'en' => 'Boulevard du Mont Boron'],
                    'ville' => ['fr' => 'Nice', 'en' => 'Nice'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => 'Boulevard du Mont Boron, 06300 Nice, France', 'en' => 'Boulevard du Mont Boron, 06300 Nice, France'],
                    'region' => ['fr' => 'Provence-Alpes-Côte d’Azur', 'en' => 'Provence-Alpes-Côte d’Azur'],
                    'district' => ['fr' => 'Alpes-Maritimes', 'en' => 'Alpes-Maritimes'],
                    'localite' => ['fr' => 'Nice', 'en' => 'Nice'],
                    'quartier' => ['fr' => 'Mont Boron', 'en' => 'Mont Boron'],
                    'point_interet' => ['fr' => 'Fort du Mont Alban', 'en' => 'Fort du Mont Alban'],
                ],
            ],
            [
                'type_bien' => 'Bureaux',
                'type_transaction' => 'Louer',
                'surface_total' => '185',
                'montant_loyer_hors_charge' => '3700',
                'montant_des_charges' => '450',
                'montant_depot_de_garantie' => '11100',
                'annee_construction' => '2008',
                'code_postal' => '59800',
                'latitude' => '50.6366000',
                'longitude' => '3.0680000',
                'salle_de_bains' => '2',
                'dpe' => '112', 'dpe_lettre' => 'C', 'ges' => '12', 'ges_lettre' => 'C',
                'date_indexation_energie' => '2024-09-17',
                'show_adresse' => 'oui',
                'caracteristiques' => 'Ascenseur|Climatisation|Stationnement',
                'images' => '1497366216548-37526070297c|1604014237800-1c9102c219da',
                'i18n' => [
                    'titre' => ['fr' => 'Plateau de bureaux 185 m² – Gare Lille-Flandres', 'en' => '185 m² office floor – Lille-Flandres station'],
                    'description' => [
                        'fr' => 'Plateau de bureaux climatisé de 185 m² au 2e étage d’un immeuble tertiaire récent : open space modulable, trois bureaux fermés, salle de réunion, kitchenette, deux sanitaires. Fibre optique, contrôle d’accès, deux places de parking en sous-sol. À 3 minutes à pied de la gare.',
                        'en' => 'Air-conditioned 185 m² office floor on the 2nd floor of a modern office building: flexible open space, three private offices, meeting room, kitchenette, two restrooms. Fibre broadband, access control, two underground parking spaces. 3 minutes’ walk from the station.',
                    ],
                    'adresse' => ['fr' => '15 Rue Faidherbe', 'en' => '15 Rue Faidherbe'],
                    'ville' => ['fr' => 'Lille', 'en' => 'Lille'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '15 Rue Faidherbe, 59800 Lille, France', 'en' => '15 Rue Faidherbe, 59800 Lille, France'],
                    'region' => ['fr' => 'Hauts-de-France', 'en' => 'Hauts-de-France'],
                    'district' => ['fr' => 'Nord', 'en' => 'Nord'],
                    'localite' => ['fr' => 'Lille', 'en' => 'Lille'],
                    'quartier' => ['fr' => 'Lille-Centre', 'en' => 'Lille-Centre'],
                    'point_interet' => ['fr' => 'Gare Lille-Flandres', 'en' => 'Lille-Flandres station'],
                ],
            ],
            [
                'type_bien' => 'Local commercial',
                'type_transaction' => 'Louer',
                'surface_total' => '92',
                'montant_loyer_hors_charge' => '2900',
                'montant_des_charges' => '180',
                'montant_depot_de_garantie' => '8700',
                'annee_construction' => '1950',
                'code_postal' => '44000',
                'latitude' => '47.2131000',
                'longitude' => '-1.5605000',
                'salle_de_bains' => '1',
                'dpe' => '236', 'dpe_lettre' => 'D', 'ges' => '41', 'ges_lettre' => 'D',
                'date_indexation_energie' => '2024-05-02',
                'show_adresse' => 'oui',
                'caracteristiques' => 'Climatisation|Cave/débarras',
                'images' => '1441986300917-64674bd600d8|1586023492125-27b2c045efd7',
                'i18n' => [
                    'titre' => ['fr' => 'Boutique 92 m² en hyper-centre – Rue Crébillon', 'en' => '92 m² retail unit in the city centre – Rue Crébillon'],
                    'description' => [
                        'fr' => 'Local commercial en rez-de-chaussée sur l’une des rues les plus commerçantes de Nantes : 70 m² de surface de vente avec 6 m de vitrine, réserve de 22 m² en sous-sol, sanitaire. Climatisation réversible. Bail commercial 3-6-9, toutes activités sauf restauration.',
                        'en' => 'Ground-floor retail unit on one of Nantes’ busiest shopping streets: 70 m² sales area with a 6 m shop front, 22 m² basement storage, restroom. Reversible air conditioning. Standard 3-6-9 commercial lease, all trades except food service.',
                    ],
                    'adresse' => ['fr' => '4 Rue Crébillon', 'en' => '4 Rue Crébillon'],
                    'ville' => ['fr' => 'Nantes', 'en' => 'Nantes'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '4 Rue Crébillon, 44000 Nantes, France', 'en' => '4 Rue Crébillon, 44000 Nantes, France'],
                    'region' => ['fr' => 'Pays de la Loire', 'en' => 'Pays de la Loire'],
                    'district' => ['fr' => 'Loire-Atlantique', 'en' => 'Loire-Atlantique'],
                    'localite' => ['fr' => 'Nantes', 'en' => 'Nantes'],
                    'quartier' => ['fr' => 'Graslin', 'en' => 'Graslin'],
                    'point_interet' => ['fr' => 'Place Graslin', 'en' => 'Place Graslin'],
                ],
            ],
            [
                'type_bien' => 'Terrain',
                'type_transaction' => 'Acheter',
                'surface_total' => '1250',
                'prix' => '420000',
                'code_postal' => '74290',
                'latitude' => '45.8547000',
                'longitude' => '6.1950000',
                'show_adresse' => 'non',
                'images' => '1500382017468-9049fed747ef',
                'i18n' => [
                    'titre' => ['fr' => 'Terrain constructible 1 250 m² vue lac – Talloires', 'en' => '1,250 m² building plot with lake view – Talloires'],
                    'description' => [
                        'fr' => 'Rare : terrain plat et viabilisé de 1 250 m² en zone constructible, exposé sud-ouest avec vue dégagée sur le lac d’Annecy et les montagnes. Hors lotissement, CU positif. À 15 minutes d’Annecy.',
                        'en' => 'Rare opportunity: flat 1,250 m² serviced building plot, south-west facing with open views over Lake Annecy and the mountains. Not part of a housing estate, positive planning certificate. 15 minutes from Annecy.',
                    ],
                    'adresse' => ['fr' => 'Route du Port', 'en' => 'Route du Port'],
                    'ville' => ['fr' => 'Talloires-Montmin', 'en' => 'Talloires-Montmin'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => 'Route du Port, 74290 Talloires-Montmin, France', 'en' => 'Route du Port, 74290 Talloires-Montmin, France'],
                    'region' => ['fr' => 'Auvergne-Rhône-Alpes', 'en' => 'Auvergne-Rhône-Alpes'],
                    'district' => ['fr' => 'Haute-Savoie', 'en' => 'Haute-Savoie'],
                    'localite' => ['fr' => 'Talloires', 'en' => 'Talloires'],
                    'quartier' => ['fr' => 'Talloires', 'en' => 'Talloires'],
                    'point_interet' => ['fr' => 'Lac d’Annecy', 'en' => 'Lake Annecy'],
                ],
            ],
            [
                'type_bien' => 'Maison',
                'type_transaction' => 'Louer',
                'surface_total' => '110',
                'montant_loyer_hors_charge' => '1680',
                'montant_des_charges' => '40',
                'montant_depot_de_garantie' => '1680',
                'annee_construction' => '1930',
                'code_postal' => '31400',
                'latitude' => '43.5905000',
                'longitude' => '1.4560000',
                'chambres' => '3',
                'salle_de_bains' => '1',
                'dpe' => '228', 'dpe_lettre' => 'D', 'ges' => '33', 'ges_lettre' => 'D',
                'date_indexation_energie' => '2025-04-09',
                'show_adresse' => 'non',
                'caracteristiques' => 'Jardin|Terrasse|Chauffage',
                'images' => '1570129477492-45c003edd2be|1600585154340-be6161a56a0c',
                'i18n' => [
                    'titre' => ['fr' => 'Maison toulousaine avec jardin – Saint-Michel', 'en' => 'Traditional Toulouse house with garden – Saint-Michel'],
                    'description' => [
                        'fr' => 'Belle toulousaine en brique de 110 m² sur deux niveaux : double séjour, cuisine indépendante, trois chambres, salle de bains et buanderie. Jardin clos de 150 m² avec terrasse. Libre au 1er novembre, proche métro Saint-Michel et du Grand Rond.',
                        'en' => 'Charming 110 m² red-brick Toulouse house over two floors: double living room, separate kitchen, three bedrooms, bathroom and utility room. 150 m² enclosed garden with terrace. Available from 1 November, close to Saint-Michel metro and the Grand Rond.',
                    ],
                    'adresse' => ['fr' => '22 Rue des Récollets', 'en' => '22 Rue des Récollets'],
                    'ville' => ['fr' => 'Toulouse', 'en' => 'Toulouse'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '22 Rue des Récollets, 31400 Toulouse, France', 'en' => '22 Rue des Récollets, 31400 Toulouse, France'],
                    'region' => ['fr' => 'Occitanie', 'en' => 'Occitanie'],
                    'district' => ['fr' => 'Haute-Garonne', 'en' => 'Haute-Garonne'],
                    'localite' => ['fr' => 'Toulouse', 'en' => 'Toulouse'],
                    'quartier' => ['fr' => 'Saint-Michel', 'en' => 'Saint-Michel'],
                    'point_interet' => ['fr' => 'Jardin des Plantes', 'en' => 'Jardin des Plantes'],
                ],
            ],
            [
                'type_bien' => 'Appartement',
                'type_transaction' => 'Acheter',
                'surface_total' => '81,50',
                'prix' => '345000',
                'montant_des_charges' => '160',
                'annee_construction' => '1880',
                'code_postal' => '13007',
                'latitude' => '43.2905000',
                'longitude' => '5.3655000',
                'chambres' => '2',
                'salle_de_bains' => '1',
                'dpe' => '187', 'dpe_lettre' => 'D', 'ges' => '29', 'ges_lettre' => 'C',
                'date_indexation_energie' => '2025-02-14',
                'show_adresse' => 'non',
                'caracteristiques' => 'Balcon|Cave/débarras',
                'images' => '1545324418-cc1a3fa10c00|1502672260266-1c1ef2d93688',
                'i18n' => [
                    'titre' => ['fr' => 'T3 avec balcon vue Vieux-Port – Rue Sainte', 'en' => '2-bedroom flat with balcony overlooking the Old Port – Rue Sainte'],
                    'description' => [
                        'fr' => 'Dans un immeuble marseillais trois fenêtres, appartement de 81 m² au 3e étage : séjour ouvrant sur un balcon filant avec vue sur le Vieux-Port et Notre-Dame-de-la-Garde, cuisine séparée, deux chambres, salle de bains. Tomettes et hauteur sous plafond de 3,20 m. Cave.',
                        'en' => 'In a classic Marseille “three-window” building, an 81 m² apartment on the 3rd floor: living room opening onto a full-width balcony overlooking the Old Port and Notre-Dame-de-la-Garde, separate kitchen, two bedrooms, bathroom. Terracotta floor tiles and 3.20 m ceilings. Cellar.',
                    ],
                    'adresse' => ['fr' => '40 Rue Sainte', 'en' => '40 Rue Sainte'],
                    'ville' => ['fr' => 'Marseille', 'en' => 'Marseille'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '40 Rue Sainte, 13007 Marseille, France', 'en' => '40 Rue Sainte, 13007 Marseille, France'],
                    'region' => ['fr' => 'Provence-Alpes-Côte d’Azur', 'en' => 'Provence-Alpes-Côte d’Azur'],
                    'district' => ['fr' => 'Bouches-du-Rhône', 'en' => 'Bouches-du-Rhône'],
                    'localite' => ['fr' => 'Marseille 7e Arrondissement', 'en' => 'Marseille 7th Arrondissement'],
                    'quartier' => ['fr' => 'Saint-Victor', 'en' => 'Saint-Victor'],
                    'point_interet' => ['fr' => 'Vieux-Port', 'en' => 'Old Port'],
                ],
            ],
            [
                'type_bien' => 'Parking/Garage/Box',
                'type_transaction' => 'Louer',
                'surface_total' => '14',
                'montant_loyer_hors_charge' => '135',
                'montant_depot_de_garantie' => '135',
                'annee_construction' => '1995',
                'code_postal' => '67000',
                'latitude' => '48.5830000',
                'longitude' => '7.7450000',
                'show_adresse' => 'oui',
                'caracteristiques' => 'Stationnement',
                'images' => '1506521781263-d8422e82f27a|1449844908441-8829872d2607',
                'i18n' => [
                    'titre' => ['fr' => 'Box fermé en sous-sol – Grande Île', 'en' => 'Lock-up garage in underground car park – Grande Île'],
                    'description' => [
                        'fr' => 'Box fermé de 14 m² au 1er sous-sol d’une résidence sécurisée (portail télécommandé, éclairage automatique). Convient à une voiture et du rangement. Idéal pour les résidents de l’hyper-centre de Strasbourg.',
                        'en' => '14 m² lock-up garage on the first basement level of a secure residence (remote-controlled gate, automatic lighting). Fits one car plus storage. Ideal for residents of central Strasbourg.',
                    ],
                    'adresse' => ['fr' => '12 Rue du Fossé des Tanneurs', 'en' => '12 Rue du Fossé des Tanneurs'],
                    'ville' => ['fr' => 'Strasbourg', 'en' => 'Strasbourg'],
                    'pays' => ['fr' => 'France', 'en' => 'France'],
                    'adresse_complete' => ['fr' => '12 Rue du Fossé des Tanneurs, 67000 Strasbourg, France', 'en' => '12 Rue du Fossé des Tanneurs, 67000 Strasbourg, France'],
                    'region' => ['fr' => 'Grand Est', 'en' => 'Grand Est'],
                    'district' => ['fr' => 'Bas-Rhin', 'en' => 'Bas-Rhin'],
                    'localite' => ['fr' => 'Strasbourg', 'en' => 'Strasbourg'],
                    'quartier' => ['fr' => 'Grande Île', 'en' => 'Grande Île'],
                    'point_interet' => ['fr' => 'Place Kléber', 'en' => 'Place Kléber'],
                ],
            ],
        ];
    }
}
