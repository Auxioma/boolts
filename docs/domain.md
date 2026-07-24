# Domaine et persistance

## Entités de référence

Le mapping Doctrine chargé par l'application contient 46 entités, dont l'entité interne de VichUploader. Les entités applicatives sont organisées par domaine plutôt que par table.

## Immobilier et recherche

| Entité | Rôle |
| --- | --- |
| `User` | Compte visiteur, agence ou administrateur ; porte les rôles et le profil. |
| `Property` | Bien immobilier publié ou en cours de création. |
| `PropertyImage` | Image attachée à un bien, avec gestion d'upload. |
| `PropertyTranslation` | Contenu traduit d'un bien. |
| `PropertyView` | Trace de consultation d'un bien. |
| `Favoris` | Relation entre un visiteur et un bien sauvegardé. |
| `PropertySearchSession` | Filtre de recherche conservé pour les résultats et la carte. |
| `CategoryBien`, `CategoryBienTransaction` | Classification de bien et de transaction, avec leurs traductions. |
| `Caracteristique` | Caractéristiques rattachables aux biens. |
| `Pays`, `Devise`, `Langues`, `FuseauHoraire`, `LangueParler` | Référentiels géographiques et linguistiques. |

Les objets `FilterCityCountry` et `ModalFilter` sont des objets de formulaire/filtres : ils ne font pas partie du mapping Doctrine courant.

## Facturation

Le sous-domaine `Entity/Billing` couvre la souscription et les documents comptables :

```text
User (agence)
  -> AgencyBillingProfile
       -> AgencyPaymentMethod
       -> AgencySubscription -> AgencySubscriptionPeriod
       -> BillingTaxIdentifier

SubscriptionPlan -> SubscriptionPlanPrice

Invoice -> InvoiceLine / InvoiceDiscount / InvoiceTax
        -> Payment -> PaymentAttempt / PaymentFee / Refund
        -> CreditNote -> CreditNoteLine
```

`PaymentWebhookEvent` conserve le traitement des événements de prestataire. Les statuts, types et périodes sont des enums PHP sous `Entity/Billing/Enum`; il faut les employer à la place de chaînes libres.

Règles de représentation :

- Les valeurs monétaires sont des entiers en unité mineure (`amountMinor`, `totalMinor`, etc.).
- Une devise est référencée par `Devise`.
- Les instantanés facturation (`sellerSnapshot`, `customerSnapshot`, `taxSnapshot`) sont JSON afin de préserver l'état du document au moment de son émission.

## Boosters

Le sous-domaine `Entity/Booster` modélise des options de mise en avant : `BoosterPack`, son prix `BoosterPackPrice`, l'opération `BoosterTransaction` et son application à un bien `PropertyBoost`.

## Données techniques et transverses

| Élément | Utilité |
| --- | --- |
| `Translation` / `UserTranslation` | Traductions stockées en base. |
| `ResetPasswordRequest` | Demandes de réinitialisation gérées par SymfonyCasts ResetPassword. |
| `Contact` | Message envoyé via le formulaire de contact. |
| `HoraireOuverture` | Horaires d'une agence. |
| `TimestampableTrait` | Dates de création et de mise à jour partagées. |
| `CreatedAtTraits`, `UpdatedAtTraits`, `DeletedAtTraits`, `LastLoginAtTraits` | Traits de cycle de vie ciblés. |

## Ajouter une entité

1. Créer l'entité et son repository sous le namespace fonctionnel approprié.
2. Déclarer les relations, index et règles de suppression dans les attributs Doctrine.
3. Générer puis relire la migration : `php bin/console doctrine:migrations:diff`.
4. Ajouter une fixture si l'entité est nécessaire au parcours de démonstration.
5. Contrôler le mapping : `php bin/console doctrine:mapping:info` puis `php bin/console doctrine:schema:validate`.
