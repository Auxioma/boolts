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

namespace App\DataFixtures\Dashboard;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class ServiceMessagesTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'boost.error.not_owner' => 'Vous ne pouvez pas booster cette annonce.',
                'boost.error.not_eligible' => 'Seules les annonces publiées ou en pause peuvent être boostées.',
                'boost.error.already_boosted' => 'Cette annonce est déjà boostée.',
                'boost.error.no_balance' => 'Vous n’avez plus de boost disponible. Achetez un pack boost pour en obtenir.',
                'billing.plan_change.current_price_not_found' => 'Le tarif de l’abonnement actuel est introuvable.',
                'billing.plan_change.must_be_active' => 'L’abonnement actuel doit être actif pour programmer un changement de forfait.',
                'billing.plan_change.cancel_scheduled' => 'Une résiliation est déjà programmée ; annulez-la avant de changer de forfait.',
                'billing.plan_change.use_cancellation' => 'Utilisez la résiliation pour repasser sur l’offre gratuite.',
                'billing.plan_change.same_currency' => 'La devise du nouveau forfait doit être identique.',
                'billing.plan_change.upgrade_immediate' => 'Le passage à un forfait de montant supérieur ou égal est immédiat, pas différé.',
                'billing.plan_change.period_end_unknown' => 'La date de fin de période de l’abonnement est inconnue.',
                'billing.plan_change.none_scheduled' => 'Aucun changement de forfait n’est programmé.',
                'messaging.error.empty' => 'Le message est vide.',
                'messaging.error.too_long' => 'Le message ne doit pas dépasser %max% caractères.',
                'messaging.error.image_formats' => 'Formats acceptés : JPG, PNG, WEBP ou GIF.',
                'messaging.error.document_formats' => 'Formats acceptés : images (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument ou texte.',
                'auth.error.professional_account' => 'Ce compte est un compte professionnel. Merci de vous connecter depuis votre espace pro.',
            ],
            'en' => [
                'boost.error.not_owner' => 'You cannot boost this listing.',
                'boost.error.not_eligible' => 'Only published or paused listings can be boosted.',
                'boost.error.already_boosted' => 'This listing is already boosted.',
                'boost.error.no_balance' => 'You have no boosts left. Buy a boost pack to get more.',
                'billing.plan_change.current_price_not_found' => 'The current subscription price could not be found.',
                'billing.plan_change.must_be_active' => 'The current subscription must be active to schedule a plan change.',
                'billing.plan_change.cancel_scheduled' => 'A cancellation is already scheduled; cancel it before changing plans.',
                'billing.plan_change.use_cancellation' => 'Use cancellation to switch back to the free plan.',
                'billing.plan_change.same_currency' => 'The new plan’s currency must be the same.',
                'billing.plan_change.upgrade_immediate' => 'Switching to a plan of equal or higher price is immediate, not deferred.',
                'billing.plan_change.period_end_unknown' => 'The subscription period end date is unknown.',
                'billing.plan_change.none_scheduled' => 'No plan change is scheduled.',
                'messaging.error.empty' => 'The message is empty.',
                'messaging.error.too_long' => 'The message must not exceed %max% characters.',
                'messaging.error.image_formats' => 'Accepted formats: JPG, PNG, WEBP or GIF.',
                'messaging.error.document_formats' => 'Accepted formats: images (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument or text.',
                'auth.error.professional_account' => 'This is a professional account. Please sign in from your professional area.',
            ],
            'es' => [
                'boost.error.not_owner' => 'No puedes destacar este anuncio.',
                'boost.error.not_eligible' => 'Solo se pueden destacar los anuncios publicados o en pausa.',
                'boost.error.already_boosted' => 'Este anuncio ya está destacado.',
                'boost.error.no_balance' => 'No te quedan boosts. Compra un pack de boosts para conseguir más.',
                'billing.plan_change.current_price_not_found' => 'No se encuentra la tarifa de la suscripción actual.',
                'billing.plan_change.must_be_active' => 'La suscripción actual debe estar activa para programar un cambio de plan.',
                'billing.plan_change.cancel_scheduled' => 'Ya hay una cancelación programada; anúlala antes de cambiar de plan.',
                'billing.plan_change.use_cancellation' => 'Usa la cancelación para volver al plan gratuito.',
                'billing.plan_change.same_currency' => 'La moneda del nuevo plan debe ser la misma.',
                'billing.plan_change.upgrade_immediate' => 'El cambio a un plan de importe igual o superior es inmediato, no diferido.',
                'billing.plan_change.period_end_unknown' => 'Se desconoce la fecha de fin del período de la suscripción.',
                'billing.plan_change.none_scheduled' => 'No hay ningún cambio de plan programado.',
                'messaging.error.empty' => 'El mensaje está vacío.',
                'messaging.error.too_long' => 'El mensaje no debe superar %max% caracteres.',
                'messaging.error.image_formats' => 'Formatos aceptados: JPG, PNG, WEBP o GIF.',
                'messaging.error.document_formats' => 'Formatos aceptados: imágenes (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument o texto.',
                'auth.error.professional_account' => 'Esta es una cuenta profesional. Inicia sesión desde tu espacio profesional.',
            ],
            'de' => [
                'boost.error.not_owner' => 'Sie können diese Anzeige nicht boosten.',
                'boost.error.not_eligible' => 'Nur veröffentlichte oder pausierte Anzeigen können geboostet werden.',
                'boost.error.already_boosted' => 'Diese Anzeige ist bereits geboostet.',
                'boost.error.no_balance' => 'Sie haben keine Boosts mehr. Kaufen Sie ein Boost-Paket, um weitere zu erhalten.',
                'billing.plan_change.current_price_not_found' => 'Der Preis des aktuellen Abonnements wurde nicht gefunden.',
                'billing.plan_change.must_be_active' => 'Das aktuelle Abonnement muss aktiv sein, um einen Tarifwechsel zu planen.',
                'billing.plan_change.cancel_scheduled' => 'Es ist bereits eine Kündigung geplant; nehmen Sie sie zurück, bevor Sie den Tarif wechseln.',
                'billing.plan_change.use_cancellation' => 'Nutzen Sie die Kündigung, um zum kostenlosen Tarif zurückzukehren.',
                'billing.plan_change.same_currency' => 'Die Währung des neuen Tarifs muss identisch sein.',
                'billing.plan_change.upgrade_immediate' => 'Der Wechsel zu einem gleich teuren oder teureren Tarif erfolgt sofort, nicht verzögert.',
                'billing.plan_change.period_end_unknown' => 'Das Enddatum des Abonnementzeitraums ist unbekannt.',
                'billing.plan_change.none_scheduled' => 'Es ist kein Tarifwechsel geplant.',
                'messaging.error.empty' => 'Die Nachricht ist leer.',
                'messaging.error.too_long' => 'Die Nachricht darf %max% Zeichen nicht überschreiten.',
                'messaging.error.image_formats' => 'Akzeptierte Formate: JPG, PNG, WEBP oder GIF.',
                'messaging.error.document_formats' => 'Akzeptierte Formate: Bilder (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument oder Text.',
                'auth.error.professional_account' => 'Dies ist ein Profi-Konto. Bitte melden Sie sich in Ihrem Profi-Bereich an.',
            ],
            'pt' => [
                'boost.error.not_owner' => 'Não pode destacar este anúncio.',
                'boost.error.not_eligible' => 'Só os anúncios publicados ou em pausa podem ser destacados.',
                'boost.error.already_boosted' => 'Este anúncio já está destacado.',
                'boost.error.no_balance' => 'Já não tem boosts disponíveis. Compre um pack de boosts para obter mais.',
                'billing.plan_change.current_price_not_found' => 'Não foi possível encontrar a tarifa da subscrição atual.',
                'billing.plan_change.must_be_active' => 'A subscrição atual tem de estar ativa para agendar uma alteração de plano.',
                'billing.plan_change.cancel_scheduled' => 'Já existe um cancelamento agendado; anule-o antes de mudar de plano.',
                'billing.plan_change.use_cancellation' => 'Utilize o cancelamento para voltar ao plano gratuito.',
                'billing.plan_change.same_currency' => 'A moeda do novo plano tem de ser a mesma.',
                'billing.plan_change.upgrade_immediate' => 'A mudança para um plano de valor igual ou superior é imediata, não diferida.',
                'billing.plan_change.period_end_unknown' => 'A data de fim do período da subscrição é desconhecida.',
                'billing.plan_change.none_scheduled' => 'Nenhuma alteração de plano está agendada.',
                'messaging.error.empty' => 'A mensagem está vazia.',
                'messaging.error.too_long' => 'A mensagem não pode exceder %max% caracteres.',
                'messaging.error.image_formats' => 'Formatos aceites: JPG, PNG, WEBP ou GIF.',
                'messaging.error.document_formats' => 'Formatos aceites: imagens (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument ou texto.',
                'auth.error.professional_account' => 'Esta é uma conta profissional. Inicie sessão a partir da sua área profissional.',
            ],
            'it' => [
                'boost.error.not_owner' => 'Non puoi mettere in evidenza questo annuncio.',
                'boost.error.not_eligible' => 'Solo gli annunci pubblicati o in pausa possono essere messi in evidenza.',
                'boost.error.already_boosted' => 'Questo annuncio è già in evidenza.',
                'boost.error.no_balance' => 'Non hai più boost disponibili. Acquista un pacchetto boost per ottenerne altri.',
                'billing.plan_change.current_price_not_found' => 'Impossibile trovare la tariffa dell’abbonamento attuale.',
                'billing.plan_change.must_be_active' => 'L’abbonamento attuale deve essere attivo per programmare un cambio di piano.',
                'billing.plan_change.cancel_scheduled' => 'È già programmata una disdetta; annullala prima di cambiare piano.',
                'billing.plan_change.use_cancellation' => 'Usa la disdetta per tornare al piano gratuito.',
                'billing.plan_change.same_currency' => 'La valuta del nuovo piano deve essere la stessa.',
                'billing.plan_change.upgrade_immediate' => 'Il passaggio a un piano di importo uguale o superiore è immediato, non differito.',
                'billing.plan_change.period_end_unknown' => 'La data di fine del periodo dell’abbonamento è sconosciuta.',
                'billing.plan_change.none_scheduled' => 'Nessun cambio di piano è programmato.',
                'messaging.error.empty' => 'Il messaggio è vuoto.',
                'messaging.error.too_long' => 'Il messaggio non deve superare %max% caratteri.',
                'messaging.error.image_formats' => 'Formati accettati: JPG, PNG, WEBP o GIF.',
                'messaging.error.document_formats' => 'Formati accettati: immagini (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument o testo.',
                'auth.error.professional_account' => 'Questo è un account professionale. Accedi dalla tua area professionale.',
            ],
            'nl' => [
                'boost.error.not_owner' => 'Je kunt deze advertentie niet boosten.',
                'boost.error.not_eligible' => 'Alleen gepubliceerde of gepauzeerde advertenties kunnen worden geboost.',
                'boost.error.already_boosted' => 'Deze advertentie is al geboost.',
                'boost.error.no_balance' => 'Je hebt geen boosts meer. Koop een boostpakket om er meer te krijgen.',
                'billing.plan_change.current_price_not_found' => 'Het tarief van het huidige abonnement is niet gevonden.',
                'billing.plan_change.must_be_active' => 'Het huidige abonnement moet actief zijn om een abonnementswijziging te plannen.',
                'billing.plan_change.cancel_scheduled' => 'Er is al een opzegging gepland; annuleer die voordat je van abonnement wisselt.',
                'billing.plan_change.use_cancellation' => 'Gebruik de opzegging om terug te gaan naar het gratis abonnement.',
                'billing.plan_change.same_currency' => 'De valuta van het nieuwe abonnement moet dezelfde zijn.',
                'billing.plan_change.upgrade_immediate' => 'Overstappen naar een abonnement met een gelijke of hogere prijs gebeurt direct, niet uitgesteld.',
                'billing.plan_change.period_end_unknown' => 'De einddatum van de abonnementsperiode is onbekend.',
                'billing.plan_change.none_scheduled' => 'Er is geen abonnementswijziging gepland.',
                'messaging.error.empty' => 'Het bericht is leeg.',
                'messaging.error.too_long' => 'Het bericht mag niet meer dan %max% tekens bevatten.',
                'messaging.error.image_formats' => 'Geaccepteerde formaten: JPG, PNG, WEBP of GIF.',
                'messaging.error.document_formats' => 'Geaccepteerde formaten: afbeeldingen (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument of tekst.',
                'auth.error.professional_account' => 'Dit is een professioneel account. Log in via je professionele omgeving.',
            ],
            'hu' => [
                'boost.error.not_owner' => 'Ezt a hirdetést nem emelheti ki.',
                'boost.error.not_eligible' => 'Csak közzétett vagy szüneteltetett hirdetések emelhetők ki.',
                'boost.error.already_boosted' => 'Ez a hirdetés már ki van emelve.',
                'boost.error.no_balance' => 'Nincs több kiemelése. Vásároljon kiemeléscsomagot.',
                'billing.plan_change.current_price_not_found' => 'A jelenlegi előfizetés díja nem található.',
                'billing.plan_change.must_be_active' => 'A csomagváltás ütemezéséhez a jelenlegi előfizetésnek aktívnak kell lennie.',
                'billing.plan_change.cancel_scheduled' => 'Már van ütemezett lemondás; vonja vissza a csomagváltás előtt.',
                'billing.plan_change.use_cancellation' => 'Az ingyenes csomagra való visszatéréshez használja a lemondást.',
                'billing.plan_change.same_currency' => 'Az új csomag pénznemének azonosnak kell lennie.',
                'billing.plan_change.upgrade_immediate' => 'Az azonos vagy magasabb árú csomagra váltás azonnali, nem késleltetett.',
                'billing.plan_change.period_end_unknown' => 'Az előfizetési időszak záró dátuma ismeretlen.',
                'billing.plan_change.none_scheduled' => 'Nincs ütemezett csomagváltás.',
                'messaging.error.empty' => 'Az üzenet üres.',
                'messaging.error.too_long' => 'Az üzenet legfeljebb %max% karakter lehet.',
                'messaging.error.image_formats' => 'Elfogadott formátumok: JPG, PNG, WEBP vagy GIF.',
                'messaging.error.document_formats' => 'Elfogadott formátumok: képek (JPG, PNG, WEBP, GIF), PDF, Word, Excel, OpenDocument vagy szöveg.',
                'auth.error.professional_account' => 'Ez szakmai fiók. Kérjük, a szakmai felületen jelentkezzen be.',
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
                $translation->setPage('service_messages');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
