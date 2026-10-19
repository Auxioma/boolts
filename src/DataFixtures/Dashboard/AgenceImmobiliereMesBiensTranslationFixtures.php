<?php

/**
 * Copyright(c)2026 Boolts (https://boolts.com)
 *
 * Ce fichier fait partie d’un projet développé par Auxioma Web Agency pour l’entreprise Pastelit Co.
 * Tous droits réservés.
 */

namespace App\DataFixtures\Dashboard;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Traductions du tunnel de dépôt d'annonce (/mes/biens, étapes 1 à 8).
 */
final class AgenceImmobiliereMesBiensTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'mes_biens.page_title' => 'Mes biens immobiliers',
                'mes_biens.optional' => '(facultatif)',

                'mes_biens.stepper.go_to_step' => 'Aller à l’étape %step%',
                'mes_biens.stepper.type_bien' => 'Type de bien',
                'mes_biens.stepper.type_transaction' => 'Type de transaction',
                'mes_biens.stepper.adresse' => 'Adresse',
                'mes_biens.stepper.caracteristiques' => 'Caractéristiques',
                'mes_biens.stepper.bilan_energetique' => 'Bilan énergétique',
                'mes_biens.stepper.photos' => 'Photos',
                'mes_biens.stepper.description' => 'Description',
                'mes_biens.stepper.prix' => 'Prix',

                'mes_biens.navigation.back' => 'Retour',
                'mes_biens.navigation.save_and_exit' => 'Enregistrer et quitter',
                'mes_biens.navigation.next' => 'Suivant',
                'mes_biens.navigation.publish' => 'Publier',

                'mes_biens.limit_modal.title_line_1' => 'Vous avez atteint la limite',
                'mes_biens.limit_modal.title_line_2' => 'de dépôt d’annonces',
                'mes_biens.limit_modal.description' => 'Vous avez atteint la limite de dépôt d’annonces de votre forfait actuel. Pour pouvoir publier davantage d’annonces sur Boolts, rendez-vous sur nos offres d’abonnements.',
                'mes_biens.limit_modal.button' => 'Voir les abonnements',

                'mes_biens.type_bien.title' => 'Quel est le type de bien que vous souhaitez publier ?',
                'mes_biens.type_bien.required' => 'Veuillez sélectionner un type de bien.',

                'mes_biens.type_transaction.title' => 'Quel est le type de transaction pour ce bien ?',
                'mes_biens.type_transaction.required' => 'Veuillez sélectionner un type de transaction.',

                'mes_biens.flash.updated' => 'L’annonce a été modifiée avec succès.',

                'mes_biens.error.invalid_request' => 'Requête invalide.',
                'mes_biens.error.invalid_data' => 'Données invalides.',
                'mes_biens.error.invalid_csrf' => 'Token CSRF invalide.',
                'mes_biens.error.not_logged_in' => 'Vous devez être connecté.',

                'mes_biens.adresse.title' => 'Où se situe votre bien ?',
                'mes_biens.adresse.adresse.label' => 'Adresse du logement',
                'mes_biens.adresse.adresse.placeholder' => 'Entrez un numéro et un nom de rue',
                'mes_biens.adresse.adresse.help' => 'L’adresse est utilisée pour enrichir les informations du bien (quartier, arrondissement et code postal). Elle ne sera pas affichée publiquement par défaut.',
                'mes_biens.adresse.show_adresse' => 'Afficher publiquement l’adresse',
                'mes_biens.adresse.code_postal.label' => 'Code postal',
                'mes_biens.adresse.code_postal.placeholder' => 'Entrez un code postal',
                'mes_biens.adresse.ville.label' => 'Ville',
                'mes_biens.adresse.ville.placeholder' => 'Choisissez une ville',
                'mes_biens.adresse.pays.label' => 'Pays',
                'mes_biens.adresse.pays.placeholder' => 'Saisissez le pays',
                'mes_biens.adresse.quartier.label' => 'Quartier',
                'mes_biens.adresse.arrondissement.label' => 'Arrondissement',

                'mes_biens.caracteristiques.title' => 'Remplissez les caractéristiques de votre bien',
                'mes_biens.caracteristiques.pieces' => 'Pièces',
                'mes_biens.caracteristiques.chambre' => 'Chambre',
                'mes_biens.caracteristiques.salle_de_bain' => 'Salle de bain',
                'mes_biens.caracteristiques.salle_de_bain.help' => 'Si votre bien comprend des toilettes séparées de la salle de bain, sélectionnez la valeur de 0,5.',
                'mes_biens.caracteristiques.surface.label' => 'Surface totale*',
                'mes_biens.caracteristiques.surface.placeholder' => 'Exemple : 150',
                'mes_biens.caracteristiques.annee.label' => 'Année de construction',
                'mes_biens.caracteristiques.annee.placeholder' => 'Exemple : 1983',
                'mes_biens.caracteristiques.caracteristiques' => 'Caractéristiques',

                'mes_biens.bilan_energetique.title' => 'Indiquez les résultats de votre diagnostic de performance énergétique (DPE)',
                'mes_biens.bilan_energetique.dpe.label' => 'Consommation en énergie primaire*',
                'mes_biens.bilan_energetique.dpe.unit' => 'kWhEP/m2/an',
                'mes_biens.bilan_energetique.dpe.econome' => 'Logement économe',
                'mes_biens.bilan_energetique.dpe.energivore' => 'Logement énergivore',
                'mes_biens.bilan_energetique.ges.label' => 'Emissions de gaz à effet de serre*',
                'mes_biens.bilan_energetique.ges.unit' => 'kgCO2/m2/an',
                'mes_biens.bilan_energetique.ges.peu' => 'Peu d’émissions',
                'mes_biens.bilan_energetique.ges.beaucoup' => 'Beaucoup d’émissions',
                'mes_biens.bilan_energetique.resultat.title' => 'Résultat de votre bilan énergétique',
                'mes_biens.bilan_energetique.couts.title' => 'Estimation des coûts d\'énergie du logement',
                'mes_biens.bilan_energetique.couts.minimum' => 'Minimum',
                'mes_biens.bilan_energetique.couts.maximum' => 'Maximum',
                'mes_biens.bilan_energetique.couts.unit' => '%devise%/an',
                'mes_biens.bilan_energetique.couts.date_indexation' => 'Date d\'indexation du prix des énergies',

                'mes_biens.photos.title' => 'Téléchargez quelques photos de votre bien',
                'mes_biens.photos.warning.label' => 'Attention :',
                'mes_biens.photos.warning.text' => 'Les annonces comprenant moins de 5 images sont généralement moins attrayantes pour les particuliers et, de ce fait, moins mises en avant par Boolts. Nous vous recommandons d’ajouter au moins 5 images.',
                'mes_biens.photos.image_alt' => 'Photo du bien',
                'mes_biens.photos.actions' => 'Actions de l\'image',
                'mes_biens.photos.cover' => 'Définir comme couverture',
                'mes_biens.photos.forward' => 'Déplacer vers l’avant',
                'mes_biens.photos.backward' => 'Déplacer vers l’arrière',
                'mes_biens.photos.delete' => 'Supprimer',
                'mes_biens.photos.drop' => 'Glissez-déposer un fichier',
                'mes_biens.photos.browse' => 'Parcourir',
                'mes_biens.photos.retry' => 'Réessayer',
                'mes_biens.photos.invalid_response' => 'Réponse invalide du serveur.',
                'mes_biens.photos.upload_failed' => 'Le téléversement a échoué.',
                'mes_biens.photos.delete_failed' => 'La suppression a échoué.',
                'mes_biens.photos.error.max_images' => 'Vous ne pouvez pas dépasser %max% photos.',
                'mes_biens.photos.error.no_file' => 'Aucun fichier valide reçu.',
                'mes_biens.photos.error.not_image' => 'Le fichier doit être une image (JPG, PNG, WebP…).',
                'mes_biens.photos.error.too_large' => 'Chaque image doit peser moins de 15 Mo.',
                'mes_biens.photos.error.empty_order' => 'Ordre vide.',
                'mes_biens.photos.error.not_in_property' => 'Une des photos ne fait pas partie de ce bien.',
                'mes_biens.photos.error.not_found' => 'Photo introuvable.',
                'mes_biens.photos.error.no_current_property' => 'Aucun bien en cours.',
                'mes_biens.photos.error.forbidden' => 'Vous ne pouvez pas modifier ce bien.',

                'mes_biens.description.title' => 'Saisissez la description de votre bien',
                'mes_biens.description.titre.label' => 'Titre du logement',
                'mes_biens.description.titre.placeholder' => 'Exemple : Logement spacieux et lumineux',
                'mes_biens.description.description.label' => 'Description du logement*',
                'mes_biens.description.description.placeholder' => 'Exemple : Logement spacieux et lumineux',
                'mes_biens.description.generate_ai' => 'Générer avec IA',
                'mes_biens.description.generating' => 'Génération...',
                'mes_biens.description.reference.label' => 'Référence interne',
                'mes_biens.description.reference.placeholder' => 'Saisissez votre référence interne',
                'mes_biens.description.ai_modal.title' => 'Service momentanément indisponible',
                'mes_biens.description.ai_modal.text' => 'La génération de description par intelligence artificielle est momentanément indisponible. Merci de réessayer dans quelques instants.',
                'mes_biens.description.ai_modal.close' => 'Fermer',
                'mes_biens.description.error.not_logged_in' => 'Vous devez être connecté pour générer une description.',
                'mes_biens.description.error.no_current_property' => 'Aucun bien courant n’a été trouvé en base de données.',
                'mes_biens.description.error.forbidden' => 'Vous ne pouvez pas générer une description pour ce bien.',
                'mes_biens.description.error.no_data' => 'Aucune donnée du bien n’a été trouvée en base de données.',
                'mes_biens.description.error.service_unavailable' => 'Service momentanément indisponible.',

                'mes_biens.prix.title' => 'Définissez le prix de votre bien',
                'mes_biens.prix.prix.label' => 'Prix du logement*',
                'mes_biens.prix.per_month' => '%devise%/mois',
                'mes_biens.prix.loyer.label' => 'Montant du loyer hors charges*',
                'mes_biens.prix.charges.label' => 'Montant des charges*',
                'mes_biens.prix.loyer_affiche' => 'Loyer affiché sur l’annonce :',
                'mes_biens.prix.cc_mois' => 'CC/mois',
                'mes_biens.prix.depot.label' => 'Montant du dépôt de garantie',
                'mes_biens.prix.reglementation' => 'Je reconnais avoir été informé(e) de la réglementation applicable à l’encadrement des loyers et déclare que le bien concerné respecte les dispositions légales en vigueur. La responsabilité liée à la conformité du montant du loyer renseigné incombe exclusivement au propriétaire/bailleur.',
            ],
            'en' => [
                'mes_biens.page_title' => 'My properties',
                'mes_biens.optional' => '(optional)',

                'mes_biens.stepper.go_to_step' => 'Go to step %step%',
                'mes_biens.stepper.type_bien' => 'Property type',
                'mes_biens.stepper.type_transaction' => 'Transaction type',
                'mes_biens.stepper.adresse' => 'Address',
                'mes_biens.stepper.caracteristiques' => 'Features',
                'mes_biens.stepper.bilan_energetique' => 'Energy assessment',
                'mes_biens.stepper.photos' => 'Photos',
                'mes_biens.stepper.description' => 'Description',
                'mes_biens.stepper.prix' => 'Price',

                'mes_biens.navigation.back' => 'Back',
                'mes_biens.navigation.save_and_exit' => 'Save and exit',
                'mes_biens.navigation.next' => 'Next',
                'mes_biens.navigation.publish' => 'Publish',

                'mes_biens.limit_modal.title_line_1' => 'You have reached the limit',
                'mes_biens.limit_modal.title_line_2' => 'for posting listings',
                'mes_biens.limit_modal.description' => 'You have reached the listing limit of your current plan. To publish more listings on Boolts, check out our subscription offers.',
                'mes_biens.limit_modal.button' => 'View subscriptions',

                'mes_biens.type_bien.title' => 'What type of property do you want to publish?',
                'mes_biens.type_bien.required' => 'Please select a property type.',

                'mes_biens.type_transaction.title' => 'What type of transaction is this property for?',
                'mes_biens.type_transaction.required' => 'Please select a transaction type.',

                'mes_biens.flash.updated' => 'The listing has been successfully updated.',

                'mes_biens.error.invalid_request' => 'Invalid request.',
                'mes_biens.error.invalid_data' => 'Invalid data.',
                'mes_biens.error.invalid_csrf' => 'Invalid CSRF token.',
                'mes_biens.error.not_logged_in' => 'You must be logged in.',

                'mes_biens.adresse.title' => 'Where is your property located?',
                'mes_biens.adresse.adresse.label' => 'Property address',
                'mes_biens.adresse.adresse.placeholder' => 'Enter a street number and name',
                'mes_biens.adresse.adresse.help' => 'The address is used to enrich the property information (neighbourhood, district and postcode). It will not be displayed publicly by default.',
                'mes_biens.adresse.show_adresse' => 'Display the address publicly',
                'mes_biens.adresse.code_postal.label' => 'Postcode',
                'mes_biens.adresse.code_postal.placeholder' => 'Enter a postcode',
                'mes_biens.adresse.ville.label' => 'City',
                'mes_biens.adresse.ville.placeholder' => 'Choose a city',
                'mes_biens.adresse.pays.label' => 'Country',
                'mes_biens.adresse.pays.placeholder' => 'Enter the country',
                'mes_biens.adresse.quartier.label' => 'Neighbourhood',
                'mes_biens.adresse.arrondissement.label' => 'District',

                'mes_biens.caracteristiques.title' => 'Fill in the features of your property',
                'mes_biens.caracteristiques.pieces' => 'Rooms',
                'mes_biens.caracteristiques.chambre' => 'Bedroom',
                'mes_biens.caracteristiques.salle_de_bain' => 'Bathroom',
                'mes_biens.caracteristiques.salle_de_bain.help' => 'If your property has a toilet separate from the bathroom, select the value 0.5.',
                'mes_biens.caracteristiques.surface.label' => 'Total area*',
                'mes_biens.caracteristiques.surface.placeholder' => 'Example: 150',
                'mes_biens.caracteristiques.annee.label' => 'Year of construction',
                'mes_biens.caracteristiques.annee.placeholder' => 'Example: 1983',
                'mes_biens.caracteristiques.caracteristiques' => 'Features',

                'mes_biens.bilan_energetique.title' => 'Enter the results of your energy performance diagnosis (DPE)',
                'mes_biens.bilan_energetique.dpe.label' => 'Primary energy consumption*',
                'mes_biens.bilan_energetique.dpe.unit' => 'kWhEP/m2/year',
                'mes_biens.bilan_energetique.dpe.econome' => 'Energy-efficient property',
                'mes_biens.bilan_energetique.dpe.energivore' => 'Energy-intensive property',
                'mes_biens.bilan_energetique.ges.label' => 'Greenhouse gas emissions*',
                'mes_biens.bilan_energetique.ges.unit' => 'kgCO2/m2/year',
                'mes_biens.bilan_energetique.ges.peu' => 'Low emissions',
                'mes_biens.bilan_energetique.ges.beaucoup' => 'High emissions',
                'mes_biens.bilan_energetique.resultat.title' => 'Your energy assessment result',
                'mes_biens.bilan_energetique.couts.title' => 'Estimated energy costs of the property',
                'mes_biens.bilan_energetique.couts.minimum' => 'Minimum',
                'mes_biens.bilan_energetique.couts.maximum' => 'Maximum',
                'mes_biens.bilan_energetique.couts.unit' => '%devise%/year',
                'mes_biens.bilan_energetique.couts.date_indexation' => 'Energy price indexation date',

                'mes_biens.photos.title' => 'Upload a few photos of your property',
                'mes_biens.photos.warning.label' => 'Warning:',
                'mes_biens.photos.warning.text' => 'Listings with fewer than 5 images are generally less attractive to private individuals and, as a result, less promoted by Boolts. We recommend adding at least 5 images.',
                'mes_biens.photos.image_alt' => 'Property photo',
                'mes_biens.photos.actions' => 'Image actions',
                'mes_biens.photos.cover' => 'Set as cover',
                'mes_biens.photos.forward' => 'Move forward',
                'mes_biens.photos.backward' => 'Move backward',
                'mes_biens.photos.delete' => 'Delete',
                'mes_biens.photos.drop' => 'Drag and drop a file',
                'mes_biens.photos.browse' => 'Browse',
                'mes_biens.photos.retry' => 'Retry',
                'mes_biens.photos.invalid_response' => 'Invalid server response.',
                'mes_biens.photos.upload_failed' => 'The upload failed.',
                'mes_biens.photos.delete_failed' => 'The deletion failed.',
                'mes_biens.photos.error.max_images' => 'You cannot exceed %max% photos.',
                'mes_biens.photos.error.no_file' => 'No valid file received.',
                'mes_biens.photos.error.not_image' => 'The file must be an image (JPG, PNG, WebP…).',
                'mes_biens.photos.error.too_large' => 'Each image must be smaller than 15 MB.',
                'mes_biens.photos.error.empty_order' => 'Empty order.',
                'mes_biens.photos.error.not_in_property' => 'One of the photos does not belong to this property.',
                'mes_biens.photos.error.not_found' => 'Photo not found.',
                'mes_biens.photos.error.no_current_property' => 'No property in progress.',
                'mes_biens.photos.error.forbidden' => 'You cannot edit this property.',

                'mes_biens.description.title' => 'Enter the description of your property',
                'mes_biens.description.titre.label' => 'Property title',
                'mes_biens.description.titre.placeholder' => 'Example: Spacious and bright property',
                'mes_biens.description.description.label' => 'Property description*',
                'mes_biens.description.description.placeholder' => 'Example: Spacious and bright property',
                'mes_biens.description.generate_ai' => 'Generate with AI',
                'mes_biens.description.generating' => 'Generating...',
                'mes_biens.description.reference.label' => 'Internal reference',
                'mes_biens.description.reference.placeholder' => 'Enter your internal reference',
                'mes_biens.description.ai_modal.title' => 'Service temporarily unavailable',
                'mes_biens.description.ai_modal.text' => 'AI description generation is temporarily unavailable. Please try again in a few moments.',
                'mes_biens.description.ai_modal.close' => 'Close',
                'mes_biens.description.error.not_logged_in' => 'You must be logged in to generate a description.',
                'mes_biens.description.error.no_current_property' => 'No current property was found in the database.',
                'mes_biens.description.error.forbidden' => 'You cannot generate a description for this property.',
                'mes_biens.description.error.no_data' => 'No property data was found in the database.',
                'mes_biens.description.error.service_unavailable' => 'Service temporarily unavailable.',

                'mes_biens.prix.title' => 'Set the price of your property',
                'mes_biens.prix.prix.label' => 'Property price*',
                'mes_biens.prix.per_month' => '%devise%/month',
                'mes_biens.prix.loyer.label' => 'Rent excluding charges*',
                'mes_biens.prix.charges.label' => 'Amount of charges*',
                'mes_biens.prix.loyer_affiche' => 'Rent displayed on the listing:',
                'mes_biens.prix.cc_mois' => 'incl. charges/month',
                'mes_biens.prix.depot.label' => 'Security deposit amount',
                'mes_biens.prix.reglementation' => 'I acknowledge that I have been informed of the regulations applicable to rent control and declare that the property concerned complies with the legal provisions in force. Responsibility for the compliance of the rent amount entered lies exclusively with the owner/landlord.',
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
                $translation->setPage('mes_biens');
                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
