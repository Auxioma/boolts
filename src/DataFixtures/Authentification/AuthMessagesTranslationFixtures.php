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

namespace App\DataFixtures\Authentification;

use App\DataFixtures\FixtureEntityHelper;
use App\Entity\Translation;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

final class AuthMessagesTranslationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $translations = [
            'fr' => [
                'auth.flash.session_expired' => 'Session expirée. Veuillez recommencer.',
                'auth.flash.user_not_found' => 'Utilisateur introuvable.',
                'auth.flash.too_many_attempts' => 'Trop de tentatives. Veuillez demander un nouveau code.',
                'auth.flash.code_expired' => 'Le code a expiré. Demandez un nouveau code.',
                'auth.flash.code_invalid' => 'Le code saisi est invalide.',
                'auth.flash.resume_code_sent' => 'Un code de vérification vient de vous être envoyé pour reprendre votre inscription.',
                'auth.flash.account_exists' => 'Un compte existe déjà avec cette adresse. Connectez-vous ici.',
                'auth.flash.no_account' => 'Aucun compte trouvé avec cette adresse e-mail.',
                'auth.flash.no_registration' => 'Aucune inscription en cours.',
                'auth.flash.session_expired_long' => 'Votre session a expiré. Veuillez recommencer.',
                'auth.flash.wait_new_code' => 'Veuillez patienter 60 secondes avant de demander un nouveau code.',
                'auth.flash.new_code_sent' => 'Un nouveau code vous a été envoyé.',
                'contact.flash.sent' => 'Votre message a été envoyé avec succès !',
                'messaging.flash.individuals_only' => 'La messagerie avec les agences est réservée aux comptes particuliers.',
                'search.flash.select_type_country' => 'Veuillez sélectionner un type de transaction et un pays.',
                'search.error.not_found' => 'Cette recherche est introuvable ou expirée.',
                'search.error.invalid_bounds' => 'Coordonnées de carte invalides.',
                'home.error.invalid_city' => 'Ville invalide.',
            ],
            'en' => [
                'auth.flash.session_expired' => 'Session expired. Please start again.',
                'auth.flash.user_not_found' => 'User not found.',
                'auth.flash.too_many_attempts' => 'Too many attempts. Please request a new code.',
                'auth.flash.code_expired' => 'The code has expired. Request a new code.',
                'auth.flash.code_invalid' => 'The code entered is invalid.',
                'auth.flash.resume_code_sent' => 'A verification code has just been sent to you to resume your registration.',
                'auth.flash.account_exists' => 'An account already exists with this address. Sign in here.',
                'auth.flash.no_account' => 'No account found with this email address.',
                'auth.flash.no_registration' => 'No registration in progress.',
                'auth.flash.session_expired_long' => 'Your session has expired. Please start again.',
                'auth.flash.wait_new_code' => 'Please wait 60 seconds before requesting a new code.',
                'auth.flash.new_code_sent' => 'A new code has been sent to you.',
                'contact.flash.sent' => 'Your message has been sent successfully!',
                'messaging.flash.individuals_only' => 'Messaging with agencies is reserved for individual accounts.',
                'search.flash.select_type_country' => 'Please select a transaction type and a country.',
                'search.error.not_found' => 'This search could not be found or has expired.',
                'search.error.invalid_bounds' => 'Invalid map coordinates.',
                'home.error.invalid_city' => 'Invalid city.',
            ],
            'es' => [
                'auth.flash.session_expired' => 'Sesión caducada. Vuelve a empezar.',
                'auth.flash.user_not_found' => 'Usuario no encontrado.',
                'auth.flash.too_many_attempts' => 'Demasiados intentos. Solicita un nuevo código.',
                'auth.flash.code_expired' => 'El código ha caducado. Solicita un nuevo código.',
                'auth.flash.code_invalid' => 'El código introducido no es válido.',
                'auth.flash.resume_code_sent' => 'Te acabamos de enviar un código de verificación para retomar tu registro.',
                'auth.flash.account_exists' => 'Ya existe una cuenta con esta dirección. Inicia sesión aquí.',
                'auth.flash.no_account' => 'No se ha encontrado ninguna cuenta con esta dirección de correo.',
                'auth.flash.no_registration' => 'Ningún registro en curso.',
                'auth.flash.session_expired_long' => 'Tu sesión ha caducado. Vuelve a empezar.',
                'auth.flash.wait_new_code' => 'Espera 60 segundos antes de solicitar un nuevo código.',
                'auth.flash.new_code_sent' => 'Te hemos enviado un nuevo código.',
                'contact.flash.sent' => '¡Tu mensaje se ha enviado correctamente!',
                'messaging.flash.individuals_only' => 'La mensajería con las agencias está reservada a las cuentas de particulares.',
                'search.flash.select_type_country' => 'Selecciona un tipo de transacción y un país.',
                'search.error.not_found' => 'Esta búsqueda no existe o ha caducado.',
                'search.error.invalid_bounds' => 'Coordenadas del mapa no válidas.',
                'home.error.invalid_city' => 'Ciudad no válida.',
            ],
            'de' => [
                'auth.flash.session_expired' => 'Sitzung abgelaufen. Bitte beginnen Sie erneut.',
                'auth.flash.user_not_found' => 'Benutzer nicht gefunden.',
                'auth.flash.too_many_attempts' => 'Zu viele Versuche. Bitte fordern Sie einen neuen Code an.',
                'auth.flash.code_expired' => 'Der Code ist abgelaufen. Fordern Sie einen neuen Code an.',
                'auth.flash.code_invalid' => 'Der eingegebene Code ist ungültig.',
                'auth.flash.resume_code_sent' => 'Wir haben Ihnen soeben einen Bestätigungscode gesendet, um Ihre Registrierung fortzusetzen.',
                'auth.flash.account_exists' => 'Mit dieser Adresse existiert bereits ein Konto. Melden Sie sich hier an.',
                'auth.flash.no_account' => 'Kein Konto mit dieser E-Mail-Adresse gefunden.',
                'auth.flash.no_registration' => 'Keine laufende Registrierung.',
                'auth.flash.session_expired_long' => 'Ihre Sitzung ist abgelaufen. Bitte beginnen Sie erneut.',
                'auth.flash.wait_new_code' => 'Bitte warten Sie 60 Sekunden, bevor Sie einen neuen Code anfordern.',
                'auth.flash.new_code_sent' => 'Ein neuer Code wurde Ihnen gesendet.',
                'contact.flash.sent' => 'Ihre Nachricht wurde erfolgreich gesendet!',
                'messaging.flash.individuals_only' => 'Das Nachrichtensystem mit Agenturen ist Privatkonten vorbehalten.',
                'search.flash.select_type_country' => 'Bitte wählen Sie eine Transaktionsart und ein Land.',
                'search.error.not_found' => 'Diese Suche wurde nicht gefunden oder ist abgelaufen.',
                'search.error.invalid_bounds' => 'Ungültige Kartenkoordinaten.',
                'home.error.invalid_city' => 'Ungültige Stadt.',
            ],
            'pt' => [
                'auth.flash.session_expired' => 'Sessão expirada. Recomece, por favor.',
                'auth.flash.user_not_found' => 'Utilizador não encontrado.',
                'auth.flash.too_many_attempts' => 'Demasiadas tentativas. Peça um novo código.',
                'auth.flash.code_expired' => 'O código expirou. Peça um novo código.',
                'auth.flash.code_invalid' => 'O código introduzido é inválido.',
                'auth.flash.resume_code_sent' => 'Acabámos de lhe enviar um código de verificação para retomar o seu registo.',
                'auth.flash.account_exists' => 'Já existe uma conta com este endereço. Inicie sessão aqui.',
                'auth.flash.no_account' => 'Nenhuma conta encontrada com este endereço de e-mail.',
                'auth.flash.no_registration' => 'Nenhum registo em curso.',
                'auth.flash.session_expired_long' => 'A sua sessão expirou. Recomece, por favor.',
                'auth.flash.wait_new_code' => 'Aguarde 60 segundos antes de pedir um novo código.',
                'auth.flash.new_code_sent' => 'Foi-lhe enviado um novo código.',
                'contact.flash.sent' => 'A sua mensagem foi enviada com sucesso!',
                'messaging.flash.individuals_only' => 'A troca de mensagens com as agências está reservada a contas de particulares.',
                'search.flash.select_type_country' => 'Selecione um tipo de transação e um país.',
                'search.error.not_found' => 'Esta pesquisa não foi encontrada ou expirou.',
                'search.error.invalid_bounds' => 'Coordenadas do mapa inválidas.',
                'home.error.invalid_city' => 'Cidade inválida.',
            ],
            'it' => [
                'auth.flash.session_expired' => 'Sessione scaduta. Ricomincia.',
                'auth.flash.user_not_found' => 'Utente non trovato.',
                'auth.flash.too_many_attempts' => 'Troppi tentativi. Richiedi un nuovo codice.',
                'auth.flash.code_expired' => 'Il codice è scaduto. Richiedi un nuovo codice.',
                'auth.flash.code_invalid' => 'Il codice inserito non è valido.',
                'auth.flash.resume_code_sent' => 'Ti abbiamo appena inviato un codice di verifica per riprendere la registrazione.',
                'auth.flash.account_exists' => 'Esiste già un account con questo indirizzo. Accedi qui.',
                'auth.flash.no_account' => 'Nessun account trovato con questo indirizzo e-mail.',
                'auth.flash.no_registration' => 'Nessuna registrazione in corso.',
                'auth.flash.session_expired_long' => 'La tua sessione è scaduta. Ricomincia.',
                'auth.flash.wait_new_code' => 'Attendi 60 secondi prima di richiedere un nuovo codice.',
                'auth.flash.new_code_sent' => 'Ti è stato inviato un nuovo codice.',
                'contact.flash.sent' => 'Il tuo messaggio è stato inviato con successo!',
                'messaging.flash.individuals_only' => 'La messaggistica con le agenzie è riservata agli account privati.',
                'search.flash.select_type_country' => 'Seleziona un tipo di transazione e un paese.',
                'search.error.not_found' => 'Questa ricerca non è stata trovata o è scaduta.',
                'search.error.invalid_bounds' => 'Coordinate della mappa non valide.',
                'home.error.invalid_city' => 'Città non valida.',
            ],
            'nl' => [
                'auth.flash.session_expired' => 'Sessie verlopen. Begin opnieuw.',
                'auth.flash.user_not_found' => 'Gebruiker niet gevonden.',
                'auth.flash.too_many_attempts' => 'Te veel pogingen. Vraag een nieuwe code aan.',
                'auth.flash.code_expired' => 'De code is verlopen. Vraag een nieuwe code aan.',
                'auth.flash.code_invalid' => 'De ingevoerde code is ongeldig.',
                'auth.flash.resume_code_sent' => 'Er is zojuist een verificatiecode naar je verzonden om je registratie te hervatten.',
                'auth.flash.account_exists' => 'Er bestaat al een account met dit adres. Log hier in.',
                'auth.flash.no_account' => 'Geen account gevonden met dit e-mailadres.',
                'auth.flash.no_registration' => 'Geen registratie bezig.',
                'auth.flash.session_expired_long' => 'Je sessie is verlopen. Begin opnieuw.',
                'auth.flash.wait_new_code' => 'Wacht 60 seconden voordat je een nieuwe code aanvraagt.',
                'auth.flash.new_code_sent' => 'Er is een nieuwe code naar je verzonden.',
                'contact.flash.sent' => 'Je bericht is succesvol verzonden!',
                'messaging.flash.individuals_only' => 'Berichten met kantoren is voorbehouden aan particuliere accounts.',
                'search.flash.select_type_country' => 'Selecteer een transactietype en een land.',
                'search.error.not_found' => 'Deze zoekopdracht bestaat niet of is verlopen.',
                'search.error.invalid_bounds' => 'Ongeldige kaartcoördinaten.',
                'home.error.invalid_city' => 'Ongeldige plaats.',
            ],
            'hu' => [
                'auth.flash.session_expired' => 'A munkamenet lejárt. Kérjük, kezdje újra.',
                'auth.flash.user_not_found' => 'A felhasználó nem található.',
                'auth.flash.too_many_attempts' => 'Túl sok próbálkozás. Kérjen új kódot.',
                'auth.flash.code_expired' => 'A kód lejárt. Kérjen új kódot.',
                'auth.flash.code_invalid' => 'A megadott kód érvénytelen.',
                'auth.flash.resume_code_sent' => 'Ellenőrző kódot küldtünk a regisztráció folytatásához.',
                'auth.flash.account_exists' => 'Ezzel a címmel már létezik fiók. Jelentkezzen be itt.',
                'auth.flash.no_account' => 'Ezzel az e-mail-címmel nem található fiók.',
                'auth.flash.no_registration' => 'Nincs folyamatban lévő regisztráció.',
                'auth.flash.session_expired_long' => 'A munkamenete lejárt. Kérjük, kezdje újra.',
                'auth.flash.wait_new_code' => 'Várjon 60 másodpercet, mielőtt új kódot kér.',
                'auth.flash.new_code_sent' => 'Új kódot küldtünk Önnek.',
                'contact.flash.sent' => 'Üzenetét sikeresen elküldtük!',
                'messaging.flash.individuals_only' => 'Az ügynökségekkel való üzenetváltás csak magánfiókok számára érhető el.',
                'search.flash.select_type_country' => 'Válasszon tranzakciótípust és országot.',
                'search.error.not_found' => 'Ez a keresés nem található vagy lejárt.',
                'search.error.invalid_bounds' => 'Érvénytelen térképkoordináták.',
                'home.error.invalid_city' => 'Érvénytelen város.',
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
                $translation->setPage('auth_messages');

                $manager->persist($translation);
            }
        }

        $manager->flush();
    }
}
