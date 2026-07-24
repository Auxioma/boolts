# Architecture PHP

## Vue d'ensemble

Boolts est une application Symfony monolithique. Les requêtes HTTP entrent dans des contrôleurs attribués, qui délèguent la persistance à Doctrine, l'affichage à Twig ou retournent du JSON pour les interactions asynchrones.

```text
Navigateur / client HTTP
        |
        v
Routes Symfony + sécurité
        |
        v
Contrôleurs (src/Controller)
        |--------------------> Formulaires (src/Form)
        |--------------------> Services (src/Service, src/Mailer)
        v
Entités Doctrine (src/Entity) <--> Repositories (src/Repository)
        |
        v
Base relationnelle + migrations
```

## Organisation du code

| Répertoire | Responsabilité |
| --- | --- |
| `src/Controller` | Points d'entrée HTTP : public, authentification, tableau de bord, API et administration EasyAdmin. |
| `src/Entity` | Modèle Doctrine. Les sous-domaines `Billing`, `Booster`, `Search` et les enums sont séparés. |
| `src/Repository` | Requêtes Doctrine réutilisables et accès optimisé au modèle. |
| `src/Form` | Formulaires Symfony pour l'authentification, les biens, les filtres et le contact. |
| `src/Service` | Intégrations et logique applicative transverse : géolocalisation, traduction, Mapbox, slug et email. |
| `src/Security` | Authenticators visiteur, agence et Google. |
| `src/DataFixtures` | Jeu de données de développement, y compris les données de référence et la facturation. |
| `src/Mailer` | Adaptateurs d'envoi d'emails, notamment le code de double authentification. |
| `src/Vich` | Convention de répertoire des fichiers téléversés. |

## Couches et conventions

- Les contrôleurs ne doivent pas contenir de requête SQL : ils utilisent un repository ou un service.
- Les relations et contraintes de persistance sont déclarées par attributs Doctrine dans les entités.
- Les montants de facturation sont stockés en unités mineures (`*Minor`) : ne pas les convertir en flottants pour les calculs.
- Les routes sont déclarées par attributs PHP. Les routes traduites ajoutent automatiquement les variantes `fr` et `en`.
- Le conteneur injecte automatiquement les classes de `src/` grâce à `config/services.yaml`.

## Flux métier principaux

### Recherche publique

`HomeController` expose la recherche. `SearchController` reçoit le filtre, persiste une session de recherche dans `PropertySearchSession`, puis fournit les résultats et les limites de carte. `PropertyRepository` porte les requêtes de recherche ; `PublicSearchCountController` expose le comptage asynchrone.

### Publication d'un bien

L'agence crée et enrichit un `Property` dans `AgenceImmobiliereMesBiensController`. Le formulaire `MesBiensType` porte les données par étape. `PropertyImage` représente les visuels et `PropertyDirectoryNamer` détermine leur répertoire de stockage.

### Authentification

Les authenticators `VisiteurAuthenticator`, `AgenceImmobiliereAuthenticator` et `GoogleAuthenticator` partagent le fournisseur Doctrine `App\Entity\User`. Les contrôleurs dédiés gèrent inscription, vérification par code, profil et réinitialisation de mot de passe. La double authentification email est configurée dans le firewall principal.

### Souscription agence

Les endpoints de `Dashboard/Api/Billing` créent le moyen de paiement et l'abonnement Stripe. Les données persistées se trouvent sous `Entity/Billing`; les appels Stripe sont faits par `Stripe\StripeClient`, injecté depuis la variable `STRIPE_SECRET_KEY`.

## Événements et services transverses

- `TranslationSubscriber` gère les comportements liés aux traductions.
- `DatabaseTranslator` décore le service Symfony `translator` pour s'appuyer sur `TranslationRepository`.
- `EmailVerificationService` gère le cycle de vie des codes de vérification.
- `ContactMailer` envoie les messages des formulaires de contact.
- `IpLocationService`, `GeoIpLocationService`, `CloudflareLocationService` et `ForcedIpCityResolver` résolvent la localisation du visiteur selon le contexte disponible.
